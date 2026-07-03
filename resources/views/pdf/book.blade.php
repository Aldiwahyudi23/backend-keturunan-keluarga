<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    @include('pdf.style')

</head>

<body>

@include('pdf.sections.cover')

<div class="page-break"></div>

@include('pdf.sections.kata-pengantar')

<div class="page-break"></div>

@include('pdf.sections.daftar-isi')

<div class="page-break"></div>

@include('pdf.sections.pendahuluan')

<div class="page-break"></div>

@include('pdf.sections.sejarah')

<div class="page-break"></div>

@include('pdf.sections.genealogy')

<div class="page-break"></div>

@include('pdf.sections.penutup')

</body>

</html>