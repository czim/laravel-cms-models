
@if (isset($errors) && $errors->any())

    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>

        @foreach ($errors->all() as $err)
            <p>{{ $err }}</p>
        @endforeach
    </div>
@endif
