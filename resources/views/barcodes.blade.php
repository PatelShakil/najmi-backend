<!DOCTYPE html>
<html>
<head>
    <title>Barcodes</title>
</head>
<body>
    @foreach($barcodeImages as $barcodeImage)
        <div>
            {!! $barcodeImage !!}
        </div>
    @endforeach
</body>
</html>
