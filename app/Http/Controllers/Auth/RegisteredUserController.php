<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Auth\Events\Registered;
// Importamos o tipo base Response para resolver o TypeError
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use App\Mail\BemVindoEmail;
use Illuminate\Support\Facades\Mail;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Response as InertiaResponse;

class RegisteredUserController extends Controller
{
    

    /**
     * Show the registration page.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     * * CORREÇÃO: Adicionamos |Response para cobrir o retorno de Inertia::location()...->withCookie()
     */
    public function store(Request $request): RedirectResponse | InertiaResponse | Response
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cargo_id' => '2',
        ]);

        $credentials = $request->only('email', 'password');

        // Autenticação com JWT no guard correto
        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return back()->withErrors(['email' => 'Credenciais inválidas']);
        }

        // Login Laravel
        Auth::login($user);

        // Cookie JWT
        $cookie = cookie(
            'jwt_token',
            $token,
            60,
            '/',
            null,
            false,
            true,
            false,
            'Strict'
        );

        // Definir rota
        $redirectUrl = $user->cargo_id == 1
            ? route('admin.dashboard')
            : route('cliente.dashboard');

        // Enviar email
        Mail::to($user->email)->send(new BemVindoEmail($user));

        // Redirecionamento normal (mantém sessão!)
        return redirect($redirectUrl)->withCookie($cookie);

    }

    public function apiCreateUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:nexa_admin.clientes,email',
            'subdominio' => 'required|string|max:255|unique:nexa_admin.clientes,nome',
            'numero' => 'required|string|max:20|unique:nexa_admin.clientes,numero',
            'tipo_painel_id' => 'required|exists:nexa_admin.tipo_painel,id',
            'password' => 'required|string|min:8',
        ],
        [
            'email.unique' => 'O email já está em uso.',
            'subdominio.unique' => 'O subdomínio já está em uso.',
            'numero.unique' => 'O número já está em uso.',
        ]
        );

        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 422);
        }

        try {
            $tenant = Tenant::create([
                'nome' => $request->subdominio,
                'email' => $request->email,
                'numero' => $request->numero,
                'password' => $request->password,
                'tipo_painel_id' => $request->tipo_painel_id,
                'subdominio' => $request->subdominio,
            ]);

            $tenant->database = 'tenant_' . $tenant->id . '_credentials';
            $tenant->save();

            Log::info(['message' => 'Tenant criado: ' . $tenant->database]);

            $empresa = Empresa::create([
                'nome' => $request->subdominio,
                'email' => $request->email,
                'numero' => $request->numero,
                'tipo_painel_id' => $request->tipo_painel_id,
            ]);

            $empresaId = DB::connection('tenant_content')->table('empresas')
                ->where('tipo_painel_id', $request->tipo_painel_id)
                ->value('id');

            logger()->info('Empresa ID para tipo_painel_id ' . $request->tipo_painel_id . ': ' . $empresaId);

            //Criar usuário admin no tenant criado
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'numero' => $request->numero,
                'password' => $request->password,
                'cargo_id' => '1',
                'empresa_id' => $empresaId,
            ]);

            // Inserir pivot na conexão do tenant (painel_user fica em tenant_credentials;
            // a relação paineis() usaria nexa_admin por causa de TipoPainel, gerando erro)
            $user->getConnection()->table('painel_user')->insertOrIgnore([
                'user_id' => $user->id,
                'painel_id' => $request->tipo_painel_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info(['message' => 'Usuário admin criado: ' . $user->email]);

            // Provisionar subdomínio (e tunnel) em produção e se estiver habilitado a variavel PROVISION_SUBDOMAIN_IN_NON_PRODUCTION
            $shouldProvision = env('APP_ENV') == 'production'
                && filter_var(env('PROVISION_SUBDOMAIN_IN_NON_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);

            if ($shouldProvision) {
                try {
                    $baseSubdomain = $tenant->subdominio;
                    $subdomainRequest = new \Illuminate\Http\Request([
                        'tenant_id' => $tenant->id,
                        'subdomain' => $baseSubdomain,
                        'third_level' => null, // Não é necessário ter um terceiro nível para o subdomínio do tenant. se precisar, adicionar o terceiro nível.
                    ]);
                    $provisionController = new \App\Http\Controllers\SubdomainProvisionController();
                    $provisionResponse = $provisionController->create($subdomainRequest);
                    $provisionData = $provisionResponse->getData(true);
                    if (isset($provisionData['error'])) {
                        Log::error('Erro ao provisionar subdomínio: ' . $provisionData['error']);
                        return response()->json([
                            'success' => false,
                            'error' => 'Erro ao provisionar subdomínio: ' . $provisionData['error']
                        ], 500);
                    }
                } catch (\Exception $e) {
                    Log::error('Falha ao chamar SubdomainProvisionController: ' . $e->getMessage());
                }
            }

            // Enviar email        
            // Mail::to($user->email)->send(new BemVindoEmail($user));

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso'], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao criar tenant ou usuário: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao criar usuário', 'details' => $e->getMessage()], 500);
        }
    }


        public function apiGetCreateUser(): JsonResponse
    {

        return response()->json(['message' => 'Usuário criado com sucesso'], 201);
    }
}
