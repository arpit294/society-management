$user = App\Models\User::first();
Auth::login($user);
$request = Illuminate\Http\Request::create('/residents', 'GET');
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
$request->headers->set('Accept', 'application/json');
$kernel = app()->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
echo "CONTENT: " . substr($response->getContent(), 0, 1500) . "\n";
