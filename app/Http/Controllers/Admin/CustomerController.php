<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Conversation;
use App\Model\Newsletter;
use App\Model\Order;
use App\Model\PointTransitions;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{

    public function add_point(Request $request, $id)
    {
        User::where(['id' => $id])->increment('point', $request['point']);
        DB::table('point_transitions')->insert([
            'user_id' => $id,
            'description' => 'admin added this point',
            'type' => 'point_in',
            'amount' => $request['point'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        if ($request->ajax()) {
            return response()->json([
                'updated_point' => User::where(['id' => $id])->first()->point
            ]);
        }
    }

    public function removePoints(Request $request, $id)
    {
      $user = User::findOrFail($id);

      if ($user->point === 0 || $user->point < $request->points) {
        return;
      }

      $user->point = $user->point - $request->points;
      $user->save();
      DB::table('point_transitions')->insert([
        'user_id' => $id,
        'description' => 'admin remove this point',
        'type' => 'point_out',
        'amount' => $request->points,
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      Toastr::success('Customer points updated');

      return back();
    }

    public function set_point_modal_data($id)
    {
        $customer = User::find($id);
        return response()->json([
            'view' => view('admin-views.customer.partials._add-point-modal-content', compact('customer'))->render()
        ]);
    }

    public function removePointModal($id)
    {
      $customer = User::find($id);
      return response()->json([
        'view' => view('admin-views.customer.partials._remove-point-modal')->with([
          'customer' => $customer
        ])->render()
      ]);
    }

    public function customer_list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        $customerState = $request['customer_state'] ?? 'active';
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $customers = User::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $customers = new User();
        }

        $customers = $customers->with(['orders'])->latest();

        switch($customerState) {
          case 'all' :
            $customers = $customers->withTrashed();
            break;
          case 'inactive':
            $customers = $customers->onlyTrashed();
            break;
          default:
          break;
        }

        $customers = $customers->paginate(Helpers::getPagination())->appends($query_param);
        /**
         * Append total payment value to each customer
         */
        foreach($customers->items() as $customer) {
            $customer->append('totalPayment');
        }
        return view('admin-views.customer.list', compact('customers', 'search'));
    }

    public function search(Request $request)
    {
        $key = explode(' ', $request['search']);
        $customers = User::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('f_name', 'like', "%{$value}%")
                    ->orWhere('l_name', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%")
                    ->orWhere('phone', 'like', "%{$value}%");
            }
        })->get();
        return response()->json([
            'view' => view('admin-views.customer.partials._table', compact('customers'))->render(),
        ]);
    }

    public function view($id)
    {
        $customer = User::find($id);
        if (isset($customer)) {
            $orders = Order::latest()->where(['user_id' => $id])->paginate(Helpers::getPagination());
            return view('admin-views.customer.customer-view', compact('customer', 'orders'));
        }
        Toastr::error(translate('Customer not found!'));
        return back();
    }

    public function AddPoint(Request $request, $id)
    {
        $point = User::where(['id' => $id])->first()->point;

        $requestPoint = $request['point'];
        $point += $requestPoint;
        // dd($point);
        User::where(['id' => $id])->update([
            'point' => $point,
        ]);
        $p_trans = [
            'user_id' => $request['id'],
            'description' => 'admin Added point',
            'type' => 'point_in',
            'amount' => $request['point'],
            'created_at' => now(),
            'updated_at' => now(),

        ];
        DB::table('point_transitions')->insert($p_trans);

        Toastr::success(translate('Point Added Successfully !'));
        return back();

    }

    public function transaction(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $customer_ids = User::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%");
                }
            })->pluck('id')->toArray();

            $transition = PointTransitions::whereIn('id', $customer_ids);
            $query_param = ['search' => $request['search']];
        } else {
            $transition = new PointTransitions();
        }

        // $transition = DB::table('point_transitions')->get();
        $transition = $transition->with(['customer'])->latest()->paginate(Helpers::getPagination())->appends($query_param);
        return view('admin-views.customer.transaction-table', compact('transition', 'search'));
    }

    public function subscribed_emails(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $newsletters = Newsletter::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('email', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $newsletters = new Newsletter();
        }

        $newsletters = $newsletters->latest()->paginate(Helpers::getPagination())->appends($query_param);
        return view('admin-views.customer.subscribed-list', compact('newsletters', 'search'));
    }

    public function customer_transaction($id)
    {
        $search = '';
        $transition = PointTransitions::with(['customer'])->where(['user_id' => $id])->latest()->paginate(Helpers::getPagination());
        return view('admin-views.customer.transaction-table', compact('transition','search'));
    }

    public function get_user_info(Request $request)
    {
        $user = User::find($request['id']);
        $unchecked = Conversation::where(['user_id'=>$request['id'],'checked'=>0])->count();

        $output = [
            'id' => $user->id??'',
            'f_name' => $user->f_name??'',
            'l_name' => $user->l_name??'',
            'email' => $user->email??'',
            'image' => ($user && $user->image)? asset('storage/app/public/profile') . '/' . $user->image : asset('/public/assets/admin/img/160x160/img1.jpg'),
            'cm_firebase_token' => $user->cm_firebase_token??'',
            'unchecked' => $unchecked ?? 0

        ];

        $result=get_headers($output['image']);
        if(!stripos($result[0], "200 OK")) {
            $output['image'] = asset('/public/assets/admin/img/160x160/img1.jpg');
        }

        return response()->json($output);
    }

    public function message_notification(Request $request)
    {
        $user = User::find($request['id']);
        $fcm_token = $user->cm_firebase_token;

        $data = [
            'title' => 'New Message' . ($request->has('image_length') && $request->image_length > 0 ? (' (with ' . $request->image_length . ' attachment)') : ''),
            'description' => $request->message,
            'order_id' => '',
            'image' => $request->has('image_length') ? $request->image_length : null,
            'type'=>'order_status',
        ];

        try {
            Helpers::send_push_notif_to_device($fcm_token, $data);
            return $data;
        } catch (\Exception $exception) {
            return false;
        }

    }

    public function chat_image_upload(Request $request)
    {
        $id_img_names = [];
        if (!empty($request->file('images'))) {
            foreach ($request->images as $img) {
                $image = Helpers::upload('conversation/', 'png', $img);
                $image_url = asset('storage/app/public/conversation') . '/' . $image;
                array_push($id_img_names, $image_url);
            }
            $images = $id_img_names;
        } else {
            $images = null;
        }
        return response()->json(['image_urls' => $images], 200);
    }


    /**
     * Show edit form for the given customer
     *
     * @param integer $id
     * @return \Illuminate\Http\Response;
     */
    public function edit(int $id)
    {
        $user = User::findOrFail($id);
        return view("admin-views.customer.edit")->with([
            'user' => $user
        ]);
    }

    /**
     * Update the given customer data
     *
     * @param Request $request
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'f_name' => 'required|max:255',
            'l_name' => 'required|max:255',
            'email' => 'required|max:255|email',
            'phone' => 'required|numeric',
            'password' => 'nullable|confirmed',
        ]);

        $validated = $request->only([
            'f_name', 'l_name', 'email', 'phone'
        ]);

        $user = User::findOrFail($id);
        try {

            DB::beginTransaction();

            $user->update($validated);

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            DB::commit();

            return redirect(route("admin.customer.list"))->with("success", "Customer data updated");

        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Unable to update customer data');
        }
    }

    /**
     * Delete the given customer
     *
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {


        $user = User::findOrFail($id);

        $user->delete();
        Toastr::success('Customer deleted');
        return back();

    }


    public function restore(int $id)
    {
      $user = User::onlyTrashed()->findOrFail($id);

      $user->restore();

      Toastr::success('Customer restored');

      return back();

    }


    /**
     * Toggle selected customer state active or not active
     *
     * @param integer $id
     * @return Illuminate\Http\Response
     */
    public function toggleState(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->is_active) {
            $user->is_active = false;
        } else {
            $user->is_active = true;
        }

        $user->save();

        return back()->with('success', 'Customer status update');
    }
}
