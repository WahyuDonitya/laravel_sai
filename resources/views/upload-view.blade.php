<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Upload Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <form action="{{ route('post.upload') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row mt-3">
            <div class="col-6">
                <label for="sheet" class="form-label">Sheets ke <span class="text-danger"><b> *(Dimulai dari
                            0)</b></span> :
                </label>
                <div class="input-group mb-3">
                    <input type="number" class="form-control" name="sheet" required id="sheet" min="0">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gridCheck" name="format">
                    <label class="form-check-label" for="gridCheck">
                        Format tanggal salah
                    </label>
                </div>
                <br>
                <div class="input-group mb-3">
                    <input type="file" class="form-control" name="file" required>
                    <input type="submit" value="Submit" class="btn btn-primary">
                </div>
            </div>
        </div>
    </form>




    @if (session('errors'))
        <div class="mt-4">
            <h4 class="text-danger">Kesalahan Validasi:</h4>
            <ul class="text-danger">
                @foreach (session('errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mt-4">
            <h4 class="text-success">Berhasil</h4>
        </div>
    @endif


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>


</body>

</html>
