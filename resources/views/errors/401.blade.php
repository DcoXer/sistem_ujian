@include('errors.partials.themed', [
    'code' => 401,
    'title' => 'Unauthorized',
    'message' => 'Anda belum memiliki autentikasi yang valid untuk mengakses halaman ini.',
])
