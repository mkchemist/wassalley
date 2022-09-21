<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\AddOn;
use App\Model\Category;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Services\Notification\NotificationMessage;
use App\Services\Notification\NotificationService;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;
use function App\CentralLogics\translate;
use Carbon\Carbon;


class OrderController extends Controller
{
  public function list(Request $request, $status)
  {
    $query_param = [];
    $search = $request['search'];
    if ($request->has('search')) {
      $key = explode(' ', $request['search']);
      $query = Order::where(function ($q) use ($key) {
        foreach ($key as $value) {
          $q->orWhere('id', 'like', "%{$value}%")
            ->orWhere('order_status', 'like', "%{$value}%")
            ->orWhere('transaction_reference', 'like', "%{$value}%");
        }
      });
      $query_param = ['search' => $request['search']];
    } else {
      if (session()->has('branch_filter') == false) {
        session()->put('branch_filter', 0);
      }
      Order::where(['checked' => 0])->update(['checked' => 1]);

      //all branch
      if (session('branch_filter') == 0) {
        if ($status == 'schedule') {
          $query = Order::with(['customer', 'branch'])->schedule();
        } elseif ($status != 'all') {
          $query = Order::with(['customer', 'branch'])->where(['order_status' => $status])->notSchedule();
        } else {
          $query = Order::with(['customer', 'branch']);
        }
      } //selected branch
      else {
        if ($status == 'schedule') {
          $query = Order::with(['customer', 'branch'])->where('branch_id', session('branch_filter'))->schedule();
        } elseif ($status != 'all') {
          $query = Order::with(['customer', 'branch'])->where(['order_status' => $status, 'branch_id' => session('branch_filter')])->notSchedule();
        } else {
          $query = Order::with(['customer', 'branch'])->where(['branch_id' => session('branch_filter')]);
        }
      }
    }

    $orders = $query->notPos()->latest()->paginate(Helpers::getPagination())->appends($query_param);
    return view('admin-views.order.list', compact('orders', 'status', 'search'));
  }

  public function details($id)
  {
    $order = Order::with('details')->where(['id' => $id])->first();

    if (!isset($order)) {
      Toastr::info(translate('No more orders!'));
      return back();
    }

    //remaining delivery time
    $delivery_date_time =  $order['delivery_date'] . ' ' . $order['delivery_time'];
    $ordered_time = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s", strtotime($delivery_date_time)));
    $remaining_time = $ordered_time->add($order['preparation_time'], 'minute')->format('Y-m-d H:i:s');
    $order['remaining_time'] = $remaining_time;

    return view('admin-views.order.order-view', compact('order'));
  }

  public function search(Request $request)
  {
    $key = explode(' ', $request['search']);
    $orders = Order::where(function ($q) use ($key) {
      foreach ($key as $value) {
        $q->orWhere('id', 'like', "%{$value}%")
          ->orWhere('order_status', 'like', "%{$value}%")
          ->orWhere('transaction_reference', 'like', "%{$value}%");
      }
    })->get();
    return response()->json([
      'view' => view('admin-views.order.partials._table', compact('orders'))->render()
    ]);
  }

  public function status(Request $request)
  {
    $order = Order::find($request->id);
    if (($request->order_status == 'delivered' || $request->order_status == 'out_for_delivery') && $order['delivery_man_id'] == null && $order['order_type'] != 'take_away') {
      Toastr::warning(translate('Please assign delivery man first!'));
      return back();
    }
    $order->order_status = $request->order_status;
    $order->save();

    $fcm_token = null;
    if (isset($order->customer)) {
      $fcm_token = $order->customer->cm_firebase_token;
    }

    $value = Helpers::order_status_update_message($request->order_status);
    try {
      if ($value) {
        $data = [
          'title' => translate('Order'),
          'description' => $value,
          'order_id' => $order['id'],
          'image' => '',
          'type' => 'order_status',
        ];
        if (isset($fcm_token)) {
          Helpers::send_push_notif_to_device($fcm_token, $data);
        }
      }
    } catch (\Exception $e) {
      Toastr::warning(translate('Push notification send failed for Customer!'));
    }

    //delivery man notification
    if ($request->order_status == 'processing' && $order->delivery_man != null) {
      $fcm_token = $order->delivery_man->fcm_token;
      $value = translate('One of your order is in processing');
      try {
        if ($value) {
          $data = [
            'title' => translate('Order'),
            'description' => $value,
            'order_id' => $order['id'],
            'image' => '',
          ];
          Helpers::send_push_notif_to_device($fcm_token, $data);
        }
      } catch (\Exception $e) {
        Toastr::warning(translate('Push notification failed for DeliveryMan!'));
      }
    }

    Toastr::success(translate('Order status updated!'));
    return back();
  }

  public function preparation_time(Request $request, $id)
  {
    $order = Order::with(['customer'])->find($id);
    $delivery_date_time =  $order['delivery_date'] . ' ' . $order['delivery_time'];

    $ordered_time = Carbon::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s", strtotime($delivery_date_time)));
    $remaining_time = $ordered_time->add($order['preparation_time'], 'minute')->format('Y-m-d H:i:s');

    //if delivery time is not over
    if (strtotime(date('Y-m-d H:i:s')) < strtotime($remaining_time)) {
      $delivery_time = new DateTime($remaining_time); //time when preparation will be over
      $current_time = new DateTime(); // time now
      $interval = $delivery_time->diff($current_time);
      $remainingMinutes = $interval->i;
      $remainingMinutes += $interval->days * 24 * 60;
      $remainingMinutes += $interval->h * 60;

      $order->preparation_time += ($request->extra_minute - $remainingMinutes);
    } else {
      //if delivery time is over
      $delivery_time = new DateTime($remaining_time);
      $current_time = new DateTime();
      $interval = $delivery_time->diff($current_time);
      $diffInMinutes = $interval->i;
      $diffInMinutes += $interval->days * 24 * 60;
      $diffInMinutes += $interval->h * 60;

      $order->preparation_time += $diffInMinutes + $request->extra_minute;
    }
    $order->save();

    //notification send
    $customer = $order->customer;
    $fcm_token = null;
    if (isset($customer)) {
      $fcm_token = $customer->cm_firebase_token;
    }
    $value = Helpers::order_status_update_message('customer_notify_message_for_time_change');

    try {
      if ($value) {
        $data = [
          'title' => translate('Order'),
          'description' => $value,
          'order_id' => $order['id'],
          'image' => '',
          'type' => 'order_status',
        ];
        Helpers::send_push_notif_to_device($fcm_token, $data);
      } else {
        throw new \Exception(translate('failed'));
      }
    } catch (\Exception $e) {
      Toastr::warning(translate('Push notification send failed for Customer!'));
    }

    Toastr::success(translate('Order preparation time increased'));
    return back();
  }


  public function add_delivery_man($order_id, $delivery_man_id)
  {
    if ($delivery_man_id == 0) {
      return response()->json([], 401);
    }
    $order = Order::find($order_id);
    if ($order->order_status == 'delivered' || $order->order_status == 'returned' || $order->order_status == 'failed' || $order->order_status == 'canceled' || $order->order_status == 'scheduled') {
      return response()->json(['status' => false], 200);
    }
    $order->delivery_man_id = $delivery_man_id;
    $order->save();

    $fcm_token = $order->delivery_man->fcm_token;
    $customer_fcm_token = null;
    if (isset($order->customer)) {
      $customer_fcm_token = $order->customer->cm_firebase_token;
    }
    $value = Helpers::order_status_update_message('del_assign');
    try {
      if ($value) {
        $data = [
          'title' => translate('Order'),
          'description' => $value,
          'order_id' => $order_id,
          'image' => '',
          'type' => 'order_status',
        ];
        Helpers::send_push_notif_to_device($fcm_token, $data);
        if (isset($order->customer)) {
          $data['description'] = Helpers::order_status_update_message('customer_notify_message');
        }
        if (isset($customer_fcm_token)) {
          Helpers::send_push_notif_to_device($customer_fcm_token, $data);
        }
      }
    } catch (\Exception $e) {
      Toastr::warning(translate('Push notification failed for DeliveryMan!'));
    }

    return response()->json(['status' => true], 200);
  }

  public function payment_status(Request $request)
  {
    $order = Order::find($request->id);
    if ($request->payment_status == 'paid' && $order['transaction_reference'] == null && $order['payment_method'] != 'cash_on_delivery') {
      Toastr::warning(translate('Add your payment reference code first!'));
      return back();
    }
    $order->payment_status = $request->payment_status;
    $order->save();
    Toastr::success(translate('Payment status updated!'));
    return back();
  }

  public function update_shipping(Request $request, $id)
  {
    $request->validate([
      'contact_person_name' => 'required',
      'address_type' => 'required',
      'contact_person_number' => 'required|min:5|max:20',
      'address' => 'required'
    ]);

    $address = [
      'contact_person_name' => $request->contact_person_name,
      'contact_person_number' => $request->contact_person_number,
      'address_type' => $request->address_type,
      'address' => $request->address,
      'longitude' => $request->longitude,
      'latitude' => $request->latitude,
      'created_at' => now(),
      'updated_at' => now()
    ];

    DB::table('customer_addresses')->where('id', $id)->update($address);
    Toastr::success(translate('Address updated!'));
    return back();
  }

  public function generate_invoice($id)
  {
    $order = Order::where('id', $id)->first();
    return view('admin-views.order.invoice', compact('order'));
  }

  public function add_payment_ref_code(Request $request, $id)
  {
    Order::where(['id' => $id])->update([
      'transaction_reference' => $request['transaction_reference']
    ]);

    Toastr::success(translate('Payment reference code is added!'));
    return back();
  }

  public function branch_filter($id)
  {
    session()->put('branch_filter', $id);
    return back();
  }

  public function export_data()
  {
    $orders = Order::all();
    return (new FastExcel($orders))->download('orders.xlsx');
  }

  /**
   * Edit selected Order
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function edit(int $id)
  {
    $category = request('category');
    $search = request('search');

    $products = Product::when($category, function ($query) use($category){
      $query->where('category_ids', 'LIKE', "%$category%");
    })
    ->when($search, function ($query) use($search) {
      $query->where('name', 'LIKE', "%$search%")
      ->orWhereIn('id', function($sub) use ($search) {
        $sub->from('translations')->select('translationable_id')
        ->where('translationable_type', Product::class)
        ->where('value', 'LIKE', "%$search%");
      });
    })->whereNotIn('id', function($query) use ($id) {
      $query->from('order_details')->select('product_id')
      ->where('order_id', $id);
    })
    ->paginate(4);

    $order = Order::with(['details', 'customer'])->findOrFail($id);
    $categories = Category::get();
    session()->put('order_carts', $order);
    return view("admin-views.order.edit")->with([
      'products' => $products,
      'categories' => $categories
    ]);
  }

  /**
   * Update the given order
   *
   * @param \Illuminate\Http\Request $request
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, int $id)
  {
  }

  /**
   * View the given product
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function viewProduct($id)
  {
    $order_id = request()->order_id;
    $product = Product::findOrFail($id);
    $addOns = AddOn::whereIn('id', json_decode($product->add_ons))->get();
    $attributes = json_decode($product->choice_options);
    $detail = OrderDetail::where([
      'product_id' => $id,
      'order_id' => request('order_id')
    ])->first();
    $addOnPrice = $this->calculateAddOnPrice($detail);
    if ($detail) {
      $orderAddOn = json_decode($detail->add_on_ids);
      $orderAddOnQty = json_decode($detail->add_on_qtys);
      foreach($orderAddOn as $key => $id) {
          foreach($addOns as $addon) {
          if ($addon->id == $id) {
            $addon->quantity = $orderAddOnQty[$key];
          }
        }
      }
    }

    return view('admin-views.order.partials._view-order-cart')->with([
      'product' => $product,
      'attributes' => $attributes,
      'addOns' => $addOns,
      'order_id' => $order_id,
      'variations' => json_decode($product->variations),
      'detail' => $detail,
      'addOnPrice' => $addOnPrice
    ]);
  }

  /**
   * Add new product to the given order
   *
   * @param Request $request
   * @return \Illuminate\Http\Response
   */
  public function addOrderProduct(Request $request)
  {
    $product = Product::findOrFail($request->product_id);
    $choiceOptions = json_decode($product->choice_options);
    $variations = json_decode($product->variations);
    $currentVariation = implode('-', collect($choiceOptions)->map(function ($item) use ($request) {
      return $request[$item->name];
    })->toArray());

    $currentVariation = collect($variations)->filter(function ($item) use ($currentVariation) {
      //dd($item->type, $currentVariation, $item->type === $currentVariation);
      return $item->type === $currentVariation;
    })->flatten()->toArray();

    if (count($currentVariation) && count(json_decode($product['variations'], true)) > 0) {
      $price = Helpers::variation_price($product, json_encode($currentVariation));
    } else {
      $price = Helpers::set_price($product['price']);
    }

    $detail = OrderDetail::where([
      'order_id' => $request->order_id,
      'product_id' => $product->id
    ])->first();

    if (!$detail) {
      $detail = new OrderDetail();
    }

    $detail->product_id = $product->id;
    $detail->order_id = $request->order_id;
    $detail->price = $price;
    $detail->product_details = json_encode($product);
    $detail->variation = json_encode($currentVariation);
    $detail->discount_on_product = Helpers::discount_calculate($product, $price);
    $detail->discount_type = "discount_on_product";
    $detail->quantity = $request->quantity;
    $detail->tax_amount = Helpers::tax_calculate($product, $price);
    $detail->add_on_ids = json_encode(collect($request->add_on_id)->map(fn($i) => (int)$i)->toArray());
    $detail->add_on_qtys = json_encode(collect($request->add_on_qtys)->map(fn($i)=>(int)$i)->toArray());
    $detail->save();
    $this->calculateOrderTotalAmount($request->order_id);
    Toastr::success('Product Added');
    return back();
  }

  /**
   * Delete the given product from order
   *
   * @param int $id [Order Detail id]
   * @return \Illuminate\Http\Response
   */
  public function deleteOrderDetail($id)
  {
    $detail = OrderDetail::findOrFail($id);
    $order_id = $detail->order_id;
    $detail->delete();
    $this->calculateOrderTotalAmount($order_id);
    Toastr::success('Order Item deleted');
    return back();
  }

  /**
   * Calculate Total Order amount
   *
   * @param int $id [order id]
   * @return void
   */
  private function calculateOrderTotalAmount($id)
  {
    $order = Order::with('details')->where('id', $id)->first();
    $details = $order->details;
    $total = 0;
    $totalTax = 0;
    foreach($details as $detail) {

      $total += ($detail->price - $detail->discount_on_product) * $detail->quantity;
      $total += ($detail->tax_amount * $detail->quantity);
      $totalTax += $detail->tax_amount * $detail->quantity;
      $total += $this->calculateAddOnPrice($detail);
    }
    $total -= ($order->coupon_discount_amount+$order->extra_discount);
    $total += $order->delivery_charge;
    $order->order_amount = $total;
    $order->save();
  }

  /**
   * Calculate Order Add ons price
   *
   */
  private function calculateAddOnPrice($detail) {
    if(!$detail) {
      return 0;
    }
    $addOnIds = json_decode($detail->add_on_ids);
    $addOnQty = json_decode($detail->add_on_qtys);
    $addOnItems = AddOn::whereIn('id', $addOnIds)->get();
    $total = 0;
    foreach($addOnIds as $key => $id) {
      foreach($addOnItems as $item) {
        if ($item->id === $id) {
          $total += $item->price * $addOnQty[$key];
        }
      }
    }

    return $total;
  }

  /**
   * Send Order Edit notification for Order owner
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function sendOrderEditNotification(Request $request)
  {
    $request->validate([
      'order_id' => 'required|exists:orders,id',
      'customer_id' => 'required|exists:users,id'
    ]);

    $user = User::where('id', $request->customer_id)->firstOrFail();

    $notification = new NotificationMessage(
      "تعديل طلب {$request->order_id}",
      "تم تعديل طلب {$request->order_id}",
      "",
      [
        "order_id" => $request->order_id
      ]
    );

    NotificationService::toDevice($user->cm_firebase_token,$notification);
    Toastr::success('Notification sent');
    return back();
  }


  /* public function deleteOrder(int $id)
  {

  } */
}
