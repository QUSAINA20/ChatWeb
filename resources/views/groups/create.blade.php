<div class="container">
    <h1>Create Group</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('group.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Group Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="user_ids" class="form-label">Select Users</label>
            <select multiple class="form-control" id="user_ids" name="user_ids[]">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create Group</button>
    </form>
</div>
