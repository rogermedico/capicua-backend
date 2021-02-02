<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Roboto&family=Roboto+Slab&display=swap" rel="stylesheet">
        <link href="{{asset('css/bootstrap.min.css')}}" rel="stylesheet">

        <style>
            body {
                font-family: 'Roboto', Arial, sans-serif;
            }
            
        </style>
    </head>
    <body>


  <div class="container mt-5">
    <img class="d-block mx-auto py-4" src="{{asset('img/logo.png')}}">
    <div class="text-center h5 py-4">
      <p>L'adreça de correu <span class="email">{{ $email }}</span> s'ha verificat correctament.</p>
      <button type="button" class="btn btn-primary" onclick="location.href='{{$frontendUrl}}';">Tornar a l'inici</button>
    </div>
  </div>



<script src="{{asset('js/jquery.min.js')}}"></script>
<script src="{{asset('js/bootstrap.min.js')}}"></script>


</body>
</html>