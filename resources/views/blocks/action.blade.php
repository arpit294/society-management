<div class="d-flex gap-2 justify-content-center">
    <a href="{{ $editUrl }}" class="btn btn-sm btn-outline-primary">Edit</a>

    <form action="{{ $deleteUrl }}" method="POST" onsubmit="return confirm('Delete this block?');" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
    </form>
</div>
