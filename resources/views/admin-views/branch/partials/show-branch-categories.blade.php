<p class="lead">{{ $branch->name }} {{ translate('categories') }}</p>
<hr>
<form action="{{ route('admin.branch-categories.update') }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
    <div style="min-height: 400px">
        <x-category-tree-component :categories="$categories->toArray()" :branch-categories="$branchCategories->toArray()" />
    </div>
    <hr>
    <div class="">
        <button class="btn btn-primary">
            <i class="tio-save"></i> {{ translate('save') }}
        </button>
        <a href="{{ url()->previous() }}" class="btn btn-dark"><i class="tio-redo"></i> {{ translate('back') }}</a>
    </div>
</form>

<script defer>

  var treeCheckInputs = Array.prototype.slice.call(document.querySelectorAll('.tree-check-input'));

  function toggleAllChildren(id, checked) {
    var children = [].slice.call(document.querySelectorAll(`[data-parent-id="${id}"]`));
    children.forEach((child) => {
      if (checked) {
        child.click();
      }
      toggleAllChildren(child.id,checked);
      child.checked = checked;
    })
  }


  treeCheckInputs.forEach((item) => {

    var parent = item.dataset.parentId;
    var isParent = parent ? false: true;
    if (item.checked) {
      $(item.dataset.target).collapse('show');
    }

    item.addEventListener('click', function (event) {
      if (this.checked) {
        toggleAllChildren(this.id, true);
      } else {
        toggleAllChildren(this.id, false);
      }

    });
  });
</script>
