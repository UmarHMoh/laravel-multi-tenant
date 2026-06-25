# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: app-actions.spec.js >> Storefront customer action audit >> tenant storefront pages expose customer navigation/actions
- Location: tests/browser-audits/app-actions.spec.js:72:3

# Error details

```
Error: expect(locator).not.toContainText(expected) failed

Locator: locator('body')
Expected substring: not "Server Error"
Received string: "
    
        
    
        
            
                
                    
                        
                    
                

                
                    Internal Server Error
                
            

            
                


    
        
    

        
    

    

    
        
            
    

            Light
        
        
            
    

            Dark
        
        
            
    

            System
        
    

            
        
    


        
            
                
    
        
            
                
                    ErrorException
                
                
                    ErrorException
                
            
            
                Undefined array key \"name\"
            
        

        
            
                
                    GET tenant1.localhost:8000
                
            
            
                PHP 8.4.22 — Laravel 12.7.2
            
        
    


                
    
        
            
    
        
            
                Collapse
                Expand
                vendor frames

                
                    
    

                    
  

                

                
                    
  

                    
    

                
            
        

        
                                                
                    
                                            
                
                
                    
                        
                            
                                
                                    app/Services/Themes/ThemePageRenderer.php
                                    :26
                                
                            
                            
                                array_map
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Support\\Arr
                                    :609
                                
                            
                            
                                map
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Support\\Collection
                                    :800
                                
                            
                            
                                map
                            
                        
                    
                

                                                                
                    
                                                    
                                2 vendor frames collapsed
                            
                                            
                
                
                    
                        
                            
                                
                                    app/Services/Themes/ThemePageRenderer.php
                                    :16
                                
                            
                            
                                renderableSections
                            
                        
                    
                

                                                                
                    
                                            
                
                
                    
                        
                            
                                
                                    App\\Http\\Controllers\\Tenant\\HomepageController
                                    :26
                                
                            
                            
                                index
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Routing\\ControllerDispatcher
                                    :46
                                
                            
                            
                                dispatch
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Routing\\Route
                                    :265
                                
                            
                            
                                runController
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Routing\\Route
                                    :211
                                
                            
                            
                                run
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Routing\\Router
                                    :808
                                
                            
                            
                                {closure:Illuminate\\Routing\\Router::runRouteWithinStack():807}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :169
                                
                            
                            
                                {closure:Illuminate\\Pipeline\\Pipeline::prepareDestination():167}
                            
                        
                    
                

                                                                
                    
                                                    
                                5 vendor frames collapsed
                            
                                            
                
                
                    
                        
                            
                                
                                    app/Http/Middleware/EnsureTenantIsActive.php
                                    :24
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Http\\Middleware\\AddLinkHeadersForPreloadedAssets
                                    :20
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Inertia\\Middleware
                                    :86
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                                                
                    
                                                    
                                5 vendor frames collapsed
                            
                                            
                
                
                    
                        
                            
                                
                                    App\\Http\\Middleware\\HandleAppearance
                                    :21
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                                                
                    
                                                    
                                1 vendor frame collapsed
                            
                                            
                
                
                    
                        
                            
                                
                                    app/Http/Middleware/EnsureCentralAdminAuthenticated.php
                                    :14
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Routing\\Middleware\\SubstituteBindings
                                    :50
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken
                                    :87
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\View\\Middleware\\ShareErrorsFromSession
                                    :48
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Session\\Middleware\\StartSession
                                    :120
                                
                            
                            
                                handleStatefulRequest
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Session\\Middleware\\StartSession
                                    :63
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Cookie\\Middleware\\AddQueuedCookiesToResponse
                                    :36
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Cookie\\Middleware\\EncryptCookies
                                    :74
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Stancl\\Tenancy\\Middleware\\IdentificationMiddleware
                                    :36
                                
                            
                            
                                initializeTenancy
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain
                                    :37
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Stancl\\Tenancy\\Middleware\\PreventAccessFromCentralDomains
                                    :29
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :126
                                
                            
                            
                                then
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Routing\\Router
                                    :807
                                
                            
                            
                                runRouteWithinStack
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Routing\\Router
                                    :786
                                
                            
                            
                                runRoute
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Routing\\Router
                                    :750
                                
                            
                            
                                dispatchToRoute
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Routing\\Router
                                    :739
                                
                            
                            
                                dispatch
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Foundation\\Http\\Kernel
                                    :200
                                
                            
                            
                                {closure:Illuminate\\Foundation\\Http\\Kernel::dispatchToRouter():197}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :169
                                
                            
                            
                                {closure:Illuminate\\Pipeline\\Pipeline::prepareDestination():167}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest
                                    :21
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Foundation\\Http\\Middleware\\ConvertEmptyStringsToNull
                                    :31
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Foundation\\Http\\Middleware\\TransformsRequest
                                    :21
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Foundation\\Http\\Middleware\\TrimStrings
                                    :51
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Http\\Middleware\\ValidatePostSize
                                    :27
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Foundation\\Http\\Middleware\\PreventRequestsDuringMaintenance
                                    :109
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Http\\Middleware\\HandleCors
                                    :48
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Http\\Middleware\\TrustProxies
                                    :58
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Foundation\\Http\\Middleware\\InvokeDeferredCallbacks
                                    :22
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Http\\Middleware\\ValidatePathEncoding
                                    :26
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\\Pipeline\\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Pipeline\\Pipeline
                                    :126
                                
                            
                            
                                then
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Foundation\\Http\\Kernel
                                    :175
                                
                            
                            
                                sendRequestThroughRouter
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Foundation\\Http\\Kernel
                                    :144
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\\Foundation\\Application
                                    :1219
                                
                            
                            
                                handleRequest
                            
                        
                    
                

                                                                
                    
                                                    
                                48 vendor frames collapsed
                            
                                            
                
                
                    
                        
                            
                                
                                    public/index.php
                                    :20
                                
                            
                            
                                require_once
                            
                        
                    
                

                                                            
                            
                                1 vendor
                                frame collapsed
                            
                        
                                                                
                
                    
                        
                            
                                
                                    vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
                                    :23
                                
                            
                            
                                
                            
                        
                    
                

                                    
    

            
        
            
                

                                            app/Services/Themes/ThemePageRenderer.php
                    
                    :26
                
            
        
        
                            }                 return [                    'id' => $section['id'] ?? ('section_' . uniqid()),                    'type' => $section['type'],                    'name' => $schema['name'],                    'settings' => $this->settingsWithDefaults($schema, $section['settings'] ?? []),                    'blocks' => $this->visibleBlocks($section['blocks'] ?? []),                ];            })            ->filter()            ->values()            ->all();    }     public function homepageData(array $sections): array    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Collections/Arr.php
                    
                    :609
                
            
        
        
                public static function map(array $array, callable $callback)    {        $keys = array_keys($array);         try {            $items = array_map($callback, $array, $keys);        } catch (ArgumentCountError) {            $items = array_map($callback, $array);        }         return array_combine($keys, $items);    }     /**     * Run an associative map over each of the items.     *     * The callback should return an associative array with a single key/value pair. 
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Collections/Collection.php
                    
                    :800
                
            
        
        
                 * @param  callable(TValue, TKey): TMapValue  $callback     * @return static<TKey, TMapValue>     */    public function map(callable $callback)    {        return new static(Arr::map($this->items, $callback));    }     /**     * Run a dictionary map over the items.     *     * The callback should return an associative array with a single key/value pair.     *     * @template TMapToDictionaryKey of array-key     * @template TMapToDictionaryValue     *     * @param  callable(TValue, TKey): array<TMapToDictionaryKey, TMapToDictionaryValue>  $callback 
        
    
    
        
            
                

                                            app/Services/Themes/ThemePageRenderer.php
                    
                    :16
                
            
        
        
                    $registry = app(SectionRegistry::class);        $sections = $pageConfig['sections'] ?? [];         return collect($sections)            ->filter(fn ($section) => empty($section['hidden']))            ->map(function ($section) use ($registry) {                $schema = $registry->get((string) ($section['type'] ?? ''));                 if (! $schema) {                    return null;                }                 return [                    'id' => $section['id'] ?? ('section_' . uniqid()),                    'type' => $section['type'],                    'name' => $schema['name'],                    'settings' => $this->settingsWithDefaults($schema, $section['settings'] ?? []),
        
    
    
        
            
                

                                            app/Http/Controllers/Tenant/HomepageController.php
                    
                    :26
                
            
        
        
                ) {        $theme = $bootstrapper->ensureDefaultTheme();        $homepage = $theme->homepage()->first();         $pageConfig = $homepage?->published_config ?: ['sections' => []];        $sections = $renderer->renderableSections($pageConfig);        $homepageData = $renderer->homepageData($sections);         $search = trim((string) $request->query('search', ''));        $category = $request->query('category');        $sort = $request->query('sort', 'latest');         $productsQuery = Product::query()            ->with(['category', 'images'])            ->where('is_active', true);         if ($search !== '') {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php
                    
                    :46
                
            
        
        
                     if (method_exists($controller, 'callAction')) {            return $controller->callAction($method, $parameters);        }         return $controller->{$method}(...array_values($parameters));    }     /**     * Resolve the parameters for the controller.     *     * @param  \\Illuminate\\Routing\\Route  $route     * @param  mixed  $controller     * @param  string  $method     * @return array     */    protected function resolveParameters(Route $route, $controller, $method)
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Route.php
                    
                    :265
                
            
        
        
                 *     * @throws \\Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException     */    protected function runController()    {        return $this->controllerDispatcher()->dispatch(            $this, $this->getController(), $this->getControllerMethod()        );    }     /**     * Get the controller instance for the route.     *     * @return mixed     */    public function getController()    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Route.php
                    
                    :211
                
            
        
        
                {        $this->container = $this->container ?: new Container;         try {            if ($this->isControllerAction()) {                return $this->runController();            }             return $this->runCallable();        } catch (HttpResponseException $e) {            return $e->getResponse();        }    }     /**     * Checks whether the route's action is a controller.     * 
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Router.php
                    
                    :808
                
            
        
        
                     return (new Pipeline($this->container))            ->send($request)            ->through($middleware)            ->then(fn ($request) => $this->prepareResponse(                $request, $route->run()            ));    }     /**     * Gather the middleware for the given route with resolved class names.     *     * @param  \\Illuminate\\Routing\\Route  $route     * @return array     */    public function gatherRouteMiddleware(Route $route)    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :169
                
            
        
        
                 */    protected function prepareDestination(Closure $destination)    {        return function ($passable) use ($destination) {            try {                return $destination($passable);            } catch (Throwable $e) {                return $this->handleException($passable, $e);            }        };    }     /**     * Get a Closure that represents a slice of the application onion.     *     * @return \\Closure     */
        
    
    
        
            
                

                                            app/Http/Middleware/EnsureTenantIsActive.php
                    
                    :24
                
            
        
        
                    }         $tenant = Tenant::find($tenantId);         if (!$tenant || $tenant->is_active) {            return $next($request);        }         $tenantData = $tenant->data ?? [];         return Inertia::render('tenant/StoreUnavailable', [            'storeName' => $tenantData['store_name'] ?? $tenant->name,            'storeEmail' => $tenantData['store_email'] ?? $tenant->email,        ])->toResponse($request)->setStatusCode(503);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Http/Middleware/AddLinkHeadersForPreloadedAssets.php
                    
                    :20
                
            
        
        
                 * @param  \\Closure  $next     * @return \\Illuminate\\Http\\Response     */    public function handle($request, $next)    {        return tap($next($request), function ($response) {            if ($response instanceof Response && Vite::preloadedAssets() !== []) {                $response->header('Link', (new Collection(Vite::preloadedAssets()))                    ->map(fn ($attributes, $url) => \"<{$url}>; \".implode('; ', $attributes))                    ->join(', '), false);            }        });    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/inertiajs/inertia-laravel/src/Middleware.php
                    
                    :86
                
            
        
        
                    });         Inertia::share($this->share($request));        Inertia::setRootView($this->rootView($request));         $response = $next($request);        $response->headers->set('Vary', Header::INERTIA);         if (! $request->header(Header::INERTIA)) {            return $response;        }         if ($request->method() === 'GET' && $request->header(Header::VERSION, '') !== Inertia::getVersion()) {            $response = $this->onVersionChange($request, $response);        }         if ($response->isOk() && empty($response->getContent())) {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            app/Http/Middleware/HandleAppearance.php
                    
                    :21
                
            
        
        
                 */    public function handle(Request $request, Closure $next): Response    {        View::share('appearance', $request->cookie('appearance') ?? 'system');         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            app/Http/Middleware/EnsureCentralAdminAuthenticated.php
                    
                    :14
                
            
        
        
            class EnsureCentralAdminAuthenticated{    public function handle(Request $request, Closure $next): Response    {        if (! $request->is('central') && ! $request->is('central/*')) {            return $next($request);        }         if ($request->is('central/login') || $request->is('central/logout')) {            return $next($request);        }         if ($request->session()->get('central_admin_authenticated') === true) {            return $next($request);        }         return redirect('/central/login')->with('error', 'Please log in to access the central admin area.');
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php
                    
                    :50
                
            
        
        
                        }             throw $exception;        }         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php
                    
                    :87
                
            
        
        
                        $this->isReading($request) ||            $this->runningUnitTests() ||            $this->inExceptArray($request) ||            $this->tokensMatch($request)        ) {            return tap($next($request), function ($response) use ($request) {                if ($this->shouldAddXsrfTokenCookie()) {                    $this->addCookieToResponse($request, $response);                }            });        }         throw new TokenMismatchException('CSRF token mismatch.');    }     /**     * Determine if the HTTP request uses a ‘read’ verb. 
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php
                    
                    :48
                
            
        
        
                     // Putting the errors in the view for every view allows the developer to just        // assume that some errors are always available, which is convenient since        // they don't have to continually run checks for the presence of errors.         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php
                    
                    :120
                
            
        
        
                        $this->startSession($request, $session)        );         $this->collectGarbage($session);         $response = $next($request);         $this->storeCurrentUrl($request, $session);         $this->addCookieToResponse($response, $session);         // Again, if the session has been configured we will need to close out the session        // so that the attributes may be persisted to some storage medium. We will also        // add the session identifier cookie to the application response headers now.        $this->saveSession($request);         return $response;
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php
                    
                    :63
                
            
        
        
                    if ($this->manager->shouldBlock() ||            ($request->route() instanceof Route && $request->route()->locksFor())) {            return $this->handleRequestWhileBlocking($request, $session, $next);        }         return $this->handleStatefulRequest($request, $session, $next);    }     /**     * Handle the given request within session state.     *     * @param  \\Illuminate\\Http\\Request  $request     * @param  \\Illuminate\\Contracts\\Session\\Session  $session     * @param  \\Closure  $next     * @return mixed     */    protected function handleRequestWhileBlocking(Request $request, $session, Closure $next)
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php
                    
                    :36
                
            
        
        
                 * @param  \\Closure  $next     * @return mixed     */    public function handle($request, Closure $next)    {        $response = $next($request);         foreach ($this->cookies->getQueuedCookies() as $cookie) {            $response->headers->setCookie($cookie);        }         return $response;    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php
                    
                    :74
                
            
        
        
                 * @param  \\Closure  $next     * @return \\Symfony\\Component\\HttpFoundation\\Response     */    public function handle($request, Closure $next)    {        return $this->encrypt($next($this->decrypt($request)));    }     /**     * Decrypt the cookies on the request.     *     * @param  \\Symfony\\Component\\HttpFoundation\\Request  $request     * @return \\Symfony\\Component\\HttpFoundation\\Request     */    protected function decrypt(Request $request)    {        foreach ($request->cookies as $key => $cookie) {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/stancl/tenancy/src/Middleware/IdentificationMiddleware.php
                    
                    :36
                
            
        
        
                        };             return $onFail($e, $request, $next);        }         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/stancl/tenancy/src/Middleware/InitializeTenancyByDomain.php
                    
                    :37
                
            
        
        
                 * @param  \\Closure  $next     * @return mixed     */    public function handle($request, Closure $next)    {        return $this->initializeTenancy(            $request, $next, $request->getHost()        );    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/stancl/tenancy/src/Middleware/PreventAccessFromCentralDomains.php
                    
                    :29
                
            
        
        
                        };             return $abortRequest($request, $next);        }         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :126
                
            
        
        
                    $pipeline = array_reduce(            array_reverse($this->pipes()), $this->carry(), $this->prepareDestination($destination)        );         try {            return $pipeline($this->passable);        } finally {            if ($this->finally) {                ($this->finally)($this->passable);            }        }    }     /**     * Run the pipeline and return the result.     *     * @return mixed 
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Router.php
                    
                    :807
                
            
        
        
                    $middleware = $shouldSkipMiddleware ? [] : $this->gatherRouteMiddleware($route);         return (new Pipeline($this->container))            ->send($request)            ->through($middleware)            ->then(fn ($request) => $this->prepareResponse(                $request, $route->run()            ));    }     /**     * Gather the middleware for the given route with resolved class names.     *     * @param  \\Illuminate\\Routing\\Route  $route     * @return array     */    public function gatherRouteMiddleware(Route $route)
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Router.php
                    
                    :786
                
            
        
        
                    $request->setRouteResolver(fn () => $route);         $this->events->dispatch(new RouteMatched($route, $request));         return $this->prepareResponse($request,            $this->runRouteWithinStack($route, $request)        );    }     /**     * Run the given route within a Stack \"onion\" instance.     *     * @param  \\Illuminate\\Routing\\Route  $route     * @param  \\Illuminate\\Http\\Request  $request     * @return mixed     */    protected function runRouteWithinStack(Route $route, Request $request)
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Router.php
                    
                    :750
                
            
        
        
                 * @param  \\Illuminate\\Http\\Request  $request     * @return \\Symfony\\Component\\HttpFoundation\\Response     */    public function dispatchToRoute(Request $request)    {        return $this->runRoute($request, $this->findRoute($request));    }     /**     * Find the route matching a given request.     *     * @param  \\Illuminate\\Http\\Request  $request     * @return \\Illuminate\\Routing\\Route     */    protected function findRoute($request)    {        $this->events->dispatch(new Routing($request));
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Router.php
                    
                    :739
                
            
        
        
                 */    public function dispatch(Request $request)    {        $this->currentRequest = $request;         return $this->dispatchToRoute($request);    }     /**     * Dispatch the request to a route and return the response.     *     * @param  \\Illuminate\\Http\\Request  $request     * @return \\Symfony\\Component\\HttpFoundation\\Response     */    public function dispatchToRoute(Request $request)    {        return $this->runRoute($request, $this->findRoute($request));
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php
                    
                    :200
                
            
        
        
                protected function dispatchToRouter()    {        return function ($request) {            $this->app->instance('request', $request);             return $this->router->dispatch($request);        };    }     /**     * Call the terminate method on any terminable middleware.     *     * @param  \\Illuminate\\Http\\Request  $request     * @param  \\Illuminate\\Http\\Response  $response     * @return void     */    public function terminate($request, $response)
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :169
                
            
        
        
                 */    protected function prepareDestination(Closure $destination)    {        return function ($passable) use ($destination) {            try {                return $destination($passable);            } catch (Throwable $e) {                return $this->handleException($passable, $e);            }        };    }     /**     * Get a Closure that represents a slice of the application onion.     *     * @return \\Closure     */
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php
                    
                    :21
                
            
        
        
                 */    public function handle($request, Closure $next)    {        $this->clean($request);         return $next($request);    }     /**     * Clean the request's data.     *     * @param  \\Illuminate\\Http\\Request  $request     * @return void     */    protected function clean($request)    {        $this->cleanParameterBag($request->query);
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php
                    
                    :31
                
            
        
        
                        if ($callback($request)) {                return $next($request);            }        }         return parent::handle($request, $next);    }     /**     * Transform the given value.     *     * @param  string  $key     * @param  mixed  $value     * @return mixed     */    protected function transform($key, $value)    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php
                    
                    :21
                
            
        
        
                 */    public function handle($request, Closure $next)    {        $this->clean($request);         return $next($request);    }     /**     * Clean the request's data.     *     * @param  \\Illuminate\\Http\\Request  $request     * @return void     */    protected function clean($request)    {        $this->cleanParameterBag($request->query);
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php
                    
                    :51
                
            
        
        
                        if ($callback($request)) {                return $next($request);            }        }         return parent::handle($request, $next);    }     /**     * Transform the given value.     *     * @param  string  $key     * @param  mixed  $value     * @return mixed     */    protected function transform($key, $value)    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php
                    
                    :27
                
            
        
        
                     if ($max > 0 && $request->server('CONTENT_LENGTH') > $max) {            throw new PostTooLargeException('The POST data is too large.');        }         return $next($request);    }     /**     * Determine the server 'post_max_size' as bytes.     *     * @return int     */    protected function getPostMaxSize()    {        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {            return (int) $postMaxSize;
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php
                    
                    :109
                
            
        
        
                            null,                $this->getHeaders($data)            );        }         return $next($request);    }     /**     * Determine if the incoming request has a maintenance mode bypass cookie.     *     * @param  \\Illuminate\\Http\\Request  $request     * @param  array  $data     * @return bool     */    protected function hasValidBypassCookie($request, array $data)    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php
                    
                    :48
                
            
        
        
                 * @return \\Illuminate\\Http\\Response     */    public function handle($request, Closure $next)    {        if (! $this->hasMatchingPath($request)) {            return $next($request);        }         $this->cors->setOptions($this->container['config']->get('cors', []));         if ($this->cors->isPreflightRequest($request)) {            $response = $this->cors->handlePreflightRequest($request);             $this->cors->varyHeader($response, 'Access-Control-Request-Method');             return $response;        }
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php
                    
                    :58
                
            
        
        
                {        $request::setTrustedProxies([], $this->getTrustedHeaderNames());         $this->setTrustedProxyIpAddresses($request);         return $next($request);    }     /**     * Sets the trusted proxies on the request.     *     * @param  \\Illuminate\\Http\\Request  $request     * @return void     */    protected function setTrustedProxyIpAddresses(Request $request)    {        $trustedIps = $this->proxies() ?: config('trustedproxy.proxies');
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php
                    
                    :22
                
            
        
        
                 * @param  \\Closure  $next     * @return \\Symfony\\Component\\HttpFoundation\\Response     */    public function handle(Request $request, Closure $next)    {        return $next($request);    }     /**     * Invoke the deferred callbacks.     *     * @param  \\Illuminate\\Http\\Request  $request     * @param  \\Symfony\\Component\\HttpFoundation\\Response  $response     * @return void     */    public function terminate(Request $request, Response $response)    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php
                    
                    :26
                
            
        
        
                     if (! mb_check_encoding($decodedPath, 'UTF-8')) {            throw new MalformedUrlException;        }         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :126
                
            
        
        
                    $pipeline = array_reduce(            array_reverse($this->pipes()), $this->carry(), $this->prepareDestination($destination)        );         try {            return $pipeline($this->passable);        } finally {            if ($this->finally) {                ($this->finally)($this->passable);            }        }    }     /**     * Run the pipeline and return the result.     *     * @return mixed 
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php
                    
                    :175
                
            
        
        
                    $this->bootstrap();         return (new Pipeline($this->app))            ->send($request)            ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)            ->then($this->dispatchToRouter());    }     /**     * Bootstrap the application for HTTP requests.     *     * @return void     */    public function bootstrap()    {        if (! $this->app->hasBeenBootstrapped()) {            $this->app->bootstrapWith($this->bootstrappers());
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php
                    
                    :144
                
            
        
        
                    $this->requestStartedAt = Carbon::now();         try {            $request->enableHttpMethodParameterOverride();             $response = $this->sendRequestThroughRouter($request);        } catch (Throwable $e) {            $this->reportException($e);             $response = $this->renderException($request, $e);        }         $this->app['events']->dispatch(            new RequestHandled($request, $response)        );         return $response;
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Application.php
                    
                    :1219
                
            
        
        
                 */    public function handleRequest(Request $request)    {        $kernel = $this->make(HttpKernelContract::class);         $response = $kernel->handle($request)->send();         $kernel->terminate($request, $response);    }     /**     * Handle the incoming Artisan command.     *     * @param  \\Symfony\\Component\\Console\\Input\\InputInterface  $input     * @return int     */    public function handleCommand(InputInterface $input)
        
    
    
        
            
                

                                            public/index.php
                    
                    :20
                
            
        
        
             // Bootstrap Laravel and handle the request.../** @var Application $app */$app = require_once __DIR__.'/../bootstrap/app.php'; $app->handleRequest(Request::capture());
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
                    
                    :23
                
            
        
        
            $requestMethod = $_SERVER['REQUEST_METHOD'];$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT']; file_put_contents('php://stdout', \"[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\\n\"); require_once $publicPath.'/index.php';
        
    
        
    


                
    
        Request
    

    
        GET
        /home
    

    
        Headers
    

    
                    
                
                    host
                
                
                    tenant1.localhost:8000
                
            
                    
                
                    connection
                
                
                    keep-alive
                
            
                    
                
                    sec-ch-ua
                
                
                    \"HeadlessChrome\";v=\"149\", \"Chromium\";v=\"149\", \"Not)A;Brand\";v=\"24\"
                
            
                    
                
                    sec-ch-ua-mobile
                
                
                    ?0
                
            
                    
                
                    sec-ch-ua-platform
                
                
                    \"macOS\"
                
            
                    
                
                    upgrade-insecure-requests
                
                
                    1
                
            
                    
                
                    user-agent
                
                
                    Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/149.0.7827.55 Safari/537.36
                
            
                    
                
                    accept-language
                
                
                    en-US
                
            
                    
                
                    accept
                
                
                    text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
                
            
                    
                
                    sec-fetch-site
                
                
                    none
                
            
                    
                
                    sec-fetch-mode
                
                
                    navigate
                
            
                    
                
                    sec-fetch-user
                
                
                    ?1
                
            
                    
                
                    sec-fetch-dest
                
                
                    document
                
            
                    
                
                    accept-encoding
                
                
                    gzip, deflate, br, zstd
                
            
            

    
        Body
    

    
        
            
                No body data
            
        
    



    
        Application
    

    
         Routing 
    

    
                    
                controller
                
                    App\\Http\\Controllers\\Tenant\\HomepageController@index
                
            
                    
                route name
                
                    home
                
            
                    
                middleware
                
                    web, Stancl\\Tenancy\\Middleware\\InitializeTenancyByDomain, Stancl\\Tenancy\\Middleware\\PreventAccessFromCentralDomains, App\\Http\\Middleware\\EnsureTenantIsActive
                
            
            

    
    
         Database Queries 
        
                    
    

    
                    
                
                    mysql
                    (1.14 ms)
                
                
                    select * from `tenants` where exists (select * from `domains` where `tenants`.`id` = `domains`.`tenant_id` and `domain` = 'tenant1.localhost') limit 1
                
            
                    
                
                    mysql
                    (0.37 ms)
                
                
                    select * from `domains` where `domains`.`tenant_id` in ('tenant1')
                
            
                    
                
                    mysql
                    (0.7 ms)
                
                
                    SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'tenant_tenant1'
                
            
                    
                
                    mysql
                    (0.35 ms)
                
                
                    select * from `tenants` where `tenants`.`id` = 'tenant1' limit 1
                
            
                    
                
                    tenant
                    (1.91 ms)
                
                
                    select * from `themes` where `is_active` = '1' order by `id` desc limit 1
                
            
                    
                
                    tenant
                    (2.14 ms)
                
                
                    update `themes` set `settings` = '{\"colors\":{\"primary\":\"#111827\",\"secondary\":\"#ffffff\"},\"header\":{\"enabled\":true,\"logo_text\":\"Aromniac\",\"logo_image_url\":\"https:\\/\\/www.instagram.com\\/aromaniac.tt\\/\",\"logo_position\":\"left\",\"links\":[{\"label\":\"Shop\",\"url\":\"\\/home\"},{\"label\":\"Contact\",\"url\":\"\\/pages\\/contact\"},{\"label\":\"Cart\",\"url\":\"\\/cart\"}],\"mobile_menu\":true},\"footer\":{\"enabled\":true,\"text\":\"Powered by your Hasan Marketing.\",\"links\":[{\"label\":\"Shop\",\"url\":\"\\/home\"},{\"label\":\"Contact\",\"url\":\"\\/pages\\/contact\"}]}}', `themes`.`updated_at` = '2026-06-25 17:33:23' where `id` = 1
                
            
                    
                
                    tenant
                    (0.54 ms)
                
                
                    select * from `theme_pages` where (`theme_id` = 1 and `handle` = 'home') limit 1
                
            
                    
                
                    tenant
                    (0.38 ms)
                
                
                    select * from `theme_pages` where (`theme_id` = 1 and `handle` = 'contact') limit 1
                
            
                    
                
                    tenant
                    (0.32 ms)
                
                
                    select * from `themes` where `id` = 1 limit 1
                
            
                    
                
                    tenant
                    (0.41 ms)
                
                
                    select * from `theme_pages` where `theme_pages`.`theme_id` in (1)
                
            
                    
                
                    tenant
                    (0.38 ms)
                
                
                    select * from `theme_pages` where `theme_pages`.`theme_id` = 1 and `theme_pages`.`theme_id` is not null and `type` = 'home' limit 1
                
            
            

            
        
    

    

    


"
Timeout: 10000ms

Call log:
  - Expect "not toContainText" with timeout 10000ms
  - waiting for locator('body')
    22 × locator resolved to <body class="bg-gray-200/80 font-sans antialiased dark:bg-gray-950/95">…</body>
       - unexpected value "
    
        
    
        
            
                
                    
                        
                    
                

                
                    Internal Server Error
                
            

            
                


    
        
    

        
    

    

    
        
            
    

            Light
        
        
            
    

            Dark
        
        
            
    

            System
        
    

            
        
    


        
            
                
    
        
            
                
                    ErrorException
                
                
                    ErrorException
                
            
            
                Undefined array key "name"
            
        

        
            
                
                    GET tenant1.localhost:8000
                
            
            
                PHP 8.4.22 — Laravel 12.7.2
            
        
    


                
    
        
            
    
        
            
                Collapse
                Expand
                vendor frames

                
                    
    

                    
  

                

                
                    
  

                    
    

                
            
        

        
                                                
                    
                                            
                
                
                    
                        
                            
                                
                                    app/Services/Themes/ThemePageRenderer.php
                                    :26
                                
                            
                            
                                array_map
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Support\Arr
                                    :609
                                
                            
                            
                                map
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Support\Collection
                                    :800
                                
                            
                            
                                map
                            
                        
                    
                

                                                                
                    
                                                    
                                2 vendor frames collapsed
                            
                                            
                
                
                    
                        
                            
                                
                                    app/Services/Themes/ThemePageRenderer.php
                                    :16
                                
                            
                            
                                renderableSections
                            
                        
                    
                

                                                                
                    
                                            
                
                
                    
                        
                            
                                
                                    App\Http\Controllers\Tenant\HomepageController
                                    :26
                                
                            
                            
                                index
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Routing\ControllerDispatcher
                                    :46
                                
                            
                            
                                dispatch
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Routing\Route
                                    :265
                                
                            
                            
                                runController
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Routing\Route
                                    :211
                                
                            
                            
                                run
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Routing\Router
                                    :808
                                
                            
                            
                                {closure:Illuminate\Routing\Router::runRouteWithinStack():807}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :169
                                
                            
                            
                                {closure:Illuminate\Pipeline\Pipeline::prepareDestination():167}
                            
                        
                    
                

                                                                
                    
                                                    
                                5 vendor frames collapsed
                            
                                            
                
                
                    
                        
                            
                                
                                    app/Http/Middleware/EnsureTenantIsActive.php
                                    :24
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets
                                    :20
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Inertia\Middleware
                                    :86
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                                                
                    
                                                    
                                5 vendor frames collapsed
                            
                                            
                
                
                    
                        
                            
                                
                                    App\Http\Middleware\HandleAppearance
                                    :21
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                                                
                    
                                                    
                                1 vendor frame collapsed
                            
                                            
                
                
                    
                        
                            
                                
                                    app/Http/Middleware/EnsureCentralAdminAuthenticated.php
                                    :14
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Routing\Middleware\SubstituteBindings
                                    :50
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Foundation\Http\Middleware\VerifyCsrfToken
                                    :87
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\View\Middleware\ShareErrorsFromSession
                                    :48
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Session\Middleware\StartSession
                                    :120
                                
                            
                            
                                handleStatefulRequest
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Session\Middleware\StartSession
                                    :63
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse
                                    :36
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Cookie\Middleware\EncryptCookies
                                    :74
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Stancl\Tenancy\Middleware\IdentificationMiddleware
                                    :36
                                
                            
                            
                                initializeTenancy
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Stancl\Tenancy\Middleware\InitializeTenancyByDomain
                                    :37
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains
                                    :29
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :126
                                
                            
                            
                                then
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Routing\Router
                                    :807
                                
                            
                            
                                runRouteWithinStack
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Routing\Router
                                    :786
                                
                            
                            
                                runRoute
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Routing\Router
                                    :750
                                
                            
                            
                                dispatchToRoute
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Routing\Router
                                    :739
                                
                            
                            
                                dispatch
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Foundation\Http\Kernel
                                    :200
                                
                            
                            
                                {closure:Illuminate\Foundation\Http\Kernel::dispatchToRouter():197}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :169
                                
                            
                            
                                {closure:Illuminate\Pipeline\Pipeline::prepareDestination():167}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Foundation\Http\Middleware\TransformsRequest
                                    :21
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull
                                    :31
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Foundation\Http\Middleware\TransformsRequest
                                    :21
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Foundation\Http\Middleware\TrimStrings
                                    :51
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Http\Middleware\ValidatePostSize
                                    :27
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance
                                    :109
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Http\Middleware\HandleCors
                                    :48
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Http\Middleware\TrustProxies
                                    :58
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks
                                    :22
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Http\Middleware\ValidatePathEncoding
                                    :26
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :208
                                
                            
                            
                                {closure:{closure:Illuminate\Pipeline\Pipeline::carry():183}:184}
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Pipeline\Pipeline
                                    :126
                                
                            
                            
                                then
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Foundation\Http\Kernel
                                    :175
                                
                            
                            
                                sendRequestThroughRouter
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Foundation\Http\Kernel
                                    :144
                                
                            
                            
                                handle
                            
                        
                    
                

                                            
                
                    
                        
                            
                                
                                    Illuminate\Foundation\Application
                                    :1219
                                
                            
                            
                                handleRequest
                            
                        
                    
                

                                                                
                    
                                                    
                                48 vendor frames collapsed
                            
                                            
                
                
                    
                        
                            
                                
                                    public/index.php
                                    :20
                                
                            
                            
                                require_once
                            
                        
                    
                

                                                            
                            
                                1 vendor
                                frame collapsed
                            
                        
                                                                
                
                    
                        
                            
                                
                                    vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
                                    :23
                                
                            
                            
                                
                            
                        
                    
                

                                    
    

            
        
            
                

                                            app/Services/Themes/ThemePageRenderer.php
                    
                    :26
                
            
        
        
                            }                 return [                    'id' => $section['id'] ?? ('section_' . uniqid()),                    'type' => $section['type'],                    'name' => $schema['name'],                    'settings' => $this->settingsWithDefaults($schema, $section['settings'] ?? []),                    'blocks' => $this->visibleBlocks($section['blocks'] ?? []),                ];            })            ->filter()            ->values()            ->all();    }     public function homepageData(array $sections): array    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Collections/Arr.php
                    
                    :609
                
            
        
        
                public static function map(array $array, callable $callback)    {        $keys = array_keys($array);         try {            $items = array_map($callback, $array, $keys);        } catch (ArgumentCountError) {            $items = array_map($callback, $array);        }         return array_combine($keys, $items);    }     /**     * Run an associative map over each of the items.     *     * The callback should return an associative array with a single key/value pair. 
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Collections/Collection.php
                    
                    :800
                
            
        
        
                 * @param  callable(TValue, TKey): TMapValue  $callback     * @return static<TKey, TMapValue>     */    public function map(callable $callback)    {        return new static(Arr::map($this->items, $callback));    }     /**     * Run a dictionary map over the items.     *     * The callback should return an associative array with a single key/value pair.     *     * @template TMapToDictionaryKey of array-key     * @template TMapToDictionaryValue     *     * @param  callable(TValue, TKey): array<TMapToDictionaryKey, TMapToDictionaryValue>  $callback 
        
    
    
        
            
                

                                            app/Services/Themes/ThemePageRenderer.php
                    
                    :16
                
            
        
        
                    $registry = app(SectionRegistry::class);        $sections = $pageConfig['sections'] ?? [];         return collect($sections)            ->filter(fn ($section) => empty($section['hidden']))            ->map(function ($section) use ($registry) {                $schema = $registry->get((string) ($section['type'] ?? ''));                 if (! $schema) {                    return null;                }                 return [                    'id' => $section['id'] ?? ('section_' . uniqid()),                    'type' => $section['type'],                    'name' => $schema['name'],                    'settings' => $this->settingsWithDefaults($schema, $section['settings'] ?? []),
        
    
    
        
            
                

                                            app/Http/Controllers/Tenant/HomepageController.php
                    
                    :26
                
            
        
        
                ) {        $theme = $bootstrapper->ensureDefaultTheme();        $homepage = $theme->homepage()->first();         $pageConfig = $homepage?->published_config ?: ['sections' => []];        $sections = $renderer->renderableSections($pageConfig);        $homepageData = $renderer->homepageData($sections);         $search = trim((string) $request->query('search', ''));        $category = $request->query('category');        $sort = $request->query('sort', 'latest');         $productsQuery = Product::query()            ->with(['category', 'images'])            ->where('is_active', true);         if ($search !== '') {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php
                    
                    :46
                
            
        
        
                     if (method_exists($controller, 'callAction')) {            return $controller->callAction($method, $parameters);        }         return $controller->{$method}(...array_values($parameters));    }     /**     * Resolve the parameters for the controller.     *     * @param  \Illuminate\Routing\Route  $route     * @param  mixed  $controller     * @param  string  $method     * @return array     */    protected function resolveParameters(Route $route, $controller, $method)
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Route.php
                    
                    :265
                
            
        
        
                 *     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException     */    protected function runController()    {        return $this->controllerDispatcher()->dispatch(            $this, $this->getController(), $this->getControllerMethod()        );    }     /**     * Get the controller instance for the route.     *     * @return mixed     */    public function getController()    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Route.php
                    
                    :211
                
            
        
        
                {        $this->container = $this->container ?: new Container;         try {            if ($this->isControllerAction()) {                return $this->runController();            }             return $this->runCallable();        } catch (HttpResponseException $e) {            return $e->getResponse();        }    }     /**     * Checks whether the route's action is a controller.     * 
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Router.php
                    
                    :808
                
            
        
        
                     return (new Pipeline($this->container))            ->send($request)            ->through($middleware)            ->then(fn ($request) => $this->prepareResponse(                $request, $route->run()            ));    }     /**     * Gather the middleware for the given route with resolved class names.     *     * @param  \Illuminate\Routing\Route  $route     * @return array     */    public function gatherRouteMiddleware(Route $route)    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :169
                
            
        
        
                 */    protected function prepareDestination(Closure $destination)    {        return function ($passable) use ($destination) {            try {                return $destination($passable);            } catch (Throwable $e) {                return $this->handleException($passable, $e);            }        };    }     /**     * Get a Closure that represents a slice of the application onion.     *     * @return \Closure     */
        
    
    
        
            
                

                                            app/Http/Middleware/EnsureTenantIsActive.php
                    
                    :24
                
            
        
        
                    }         $tenant = Tenant::find($tenantId);         if (!$tenant || $tenant->is_active) {            return $next($request);        }         $tenantData = $tenant->data ?? [];         return Inertia::render('tenant/StoreUnavailable', [            'storeName' => $tenantData['store_name'] ?? $tenant->name,            'storeEmail' => $tenantData['store_email'] ?? $tenant->email,        ])->toResponse($request)->setStatusCode(503);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Http/Middleware/AddLinkHeadersForPreloadedAssets.php
                    
                    :20
                
            
        
        
                 * @param  \Closure  $next     * @return \Illuminate\Http\Response     */    public function handle($request, $next)    {        return tap($next($request), function ($response) {            if ($response instanceof Response && Vite::preloadedAssets() !== []) {                $response->header('Link', (new Collection(Vite::preloadedAssets()))                    ->map(fn ($attributes, $url) => "<{$url}>; ".implode('; ', $attributes))                    ->join(', '), false);            }        });    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/inertiajs/inertia-laravel/src/Middleware.php
                    
                    :86
                
            
        
        
                    });         Inertia::share($this->share($request));        Inertia::setRootView($this->rootView($request));         $response = $next($request);        $response->headers->set('Vary', Header::INERTIA);         if (! $request->header(Header::INERTIA)) {            return $response;        }         if ($request->method() === 'GET' && $request->header(Header::VERSION, '') !== Inertia::getVersion()) {            $response = $this->onVersionChange($request, $response);        }         if ($response->isOk() && empty($response->getContent())) {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            app/Http/Middleware/HandleAppearance.php
                    
                    :21
                
            
        
        
                 */    public function handle(Request $request, Closure $next): Response    {        View::share('appearance', $request->cookie('appearance') ?? 'system');         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            app/Http/Middleware/EnsureCentralAdminAuthenticated.php
                    
                    :14
                
            
        
        
            class EnsureCentralAdminAuthenticated{    public function handle(Request $request, Closure $next): Response    {        if (! $request->is('central') && ! $request->is('central/*')) {            return $next($request);        }         if ($request->is('central/login') || $request->is('central/logout')) {            return $next($request);        }         if ($request->session()->get('central_admin_authenticated') === true) {            return $next($request);        }         return redirect('/central/login')->with('error', 'Please log in to access the central admin area.');
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php
                    
                    :50
                
            
        
        
                        }             throw $exception;        }         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php
                    
                    :87
                
            
        
        
                        $this->isReading($request) ||            $this->runningUnitTests() ||            $this->inExceptArray($request) ||            $this->tokensMatch($request)        ) {            return tap($next($request), function ($response) use ($request) {                if ($this->shouldAddXsrfTokenCookie()) {                    $this->addCookieToResponse($request, $response);                }            });        }         throw new TokenMismatchException('CSRF token mismatch.');    }     /**     * Determine if the HTTP request uses a ‘read’ verb. 
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php
                    
                    :48
                
            
        
        
                     // Putting the errors in the view for every view allows the developer to just        // assume that some errors are always available, which is convenient since        // they don't have to continually run checks for the presence of errors.         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php
                    
                    :120
                
            
        
        
                        $this->startSession($request, $session)        );         $this->collectGarbage($session);         $response = $next($request);         $this->storeCurrentUrl($request, $session);         $this->addCookieToResponse($response, $session);         // Again, if the session has been configured we will need to close out the session        // so that the attributes may be persisted to some storage medium. We will also        // add the session identifier cookie to the application response headers now.        $this->saveSession($request);         return $response;
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php
                    
                    :63
                
            
        
        
                    if ($this->manager->shouldBlock() ||            ($request->route() instanceof Route && $request->route()->locksFor())) {            return $this->handleRequestWhileBlocking($request, $session, $next);        }         return $this->handleStatefulRequest($request, $session, $next);    }     /**     * Handle the given request within session state.     *     * @param  \Illuminate\Http\Request  $request     * @param  \Illuminate\Contracts\Session\Session  $session     * @param  \Closure  $next     * @return mixed     */    protected function handleRequestWhileBlocking(Request $request, $session, Closure $next)
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php
                    
                    :36
                
            
        
        
                 * @param  \Closure  $next     * @return mixed     */    public function handle($request, Closure $next)    {        $response = $next($request);         foreach ($this->cookies->getQueuedCookies() as $cookie) {            $response->headers->setCookie($cookie);        }         return $response;    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php
                    
                    :74
                
            
        
        
                 * @param  \Closure  $next     * @return \Symfony\Component\HttpFoundation\Response     */    public function handle($request, Closure $next)    {        return $this->encrypt($next($this->decrypt($request)));    }     /**     * Decrypt the cookies on the request.     *     * @param  \Symfony\Component\HttpFoundation\Request  $request     * @return \Symfony\Component\HttpFoundation\Request     */    protected function decrypt(Request $request)    {        foreach ($request->cookies as $key => $cookie) {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/stancl/tenancy/src/Middleware/IdentificationMiddleware.php
                    
                    :36
                
            
        
        
                        };             return $onFail($e, $request, $next);        }         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/stancl/tenancy/src/Middleware/InitializeTenancyByDomain.php
                    
                    :37
                
            
        
        
                 * @param  \Closure  $next     * @return mixed     */    public function handle($request, Closure $next)    {        return $this->initializeTenancy(            $request, $next, $request->getHost()        );    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/stancl/tenancy/src/Middleware/PreventAccessFromCentralDomains.php
                    
                    :29
                
            
        
        
                        };             return $abortRequest($request, $next);        }         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :126
                
            
        
        
                    $pipeline = array_reduce(            array_reverse($this->pipes()), $this->carry(), $this->prepareDestination($destination)        );         try {            return $pipeline($this->passable);        } finally {            if ($this->finally) {                ($this->finally)($this->passable);            }        }    }     /**     * Run the pipeline and return the result.     *     * @return mixed 
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Router.php
                    
                    :807
                
            
        
        
                    $middleware = $shouldSkipMiddleware ? [] : $this->gatherRouteMiddleware($route);         return (new Pipeline($this->container))            ->send($request)            ->through($middleware)            ->then(fn ($request) => $this->prepareResponse(                $request, $route->run()            ));    }     /**     * Gather the middleware for the given route with resolved class names.     *     * @param  \Illuminate\Routing\Route  $route     * @return array     */    public function gatherRouteMiddleware(Route $route)
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Router.php
                    
                    :786
                
            
        
        
                    $request->setRouteResolver(fn () => $route);         $this->events->dispatch(new RouteMatched($route, $request));         return $this->prepareResponse($request,            $this->runRouteWithinStack($route, $request)        );    }     /**     * Run the given route within a Stack "onion" instance.     *     * @param  \Illuminate\Routing\Route  $route     * @param  \Illuminate\Http\Request  $request     * @return mixed     */    protected function runRouteWithinStack(Route $route, Request $request)
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Router.php
                    
                    :750
                
            
        
        
                 * @param  \Illuminate\Http\Request  $request     * @return \Symfony\Component\HttpFoundation\Response     */    public function dispatchToRoute(Request $request)    {        return $this->runRoute($request, $this->findRoute($request));    }     /**     * Find the route matching a given request.     *     * @param  \Illuminate\Http\Request  $request     * @return \Illuminate\Routing\Route     */    protected function findRoute($request)    {        $this->events->dispatch(new Routing($request));
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Routing/Router.php
                    
                    :739
                
            
        
        
                 */    public function dispatch(Request $request)    {        $this->currentRequest = $request;         return $this->dispatchToRoute($request);    }     /**     * Dispatch the request to a route and return the response.     *     * @param  \Illuminate\Http\Request  $request     * @return \Symfony\Component\HttpFoundation\Response     */    public function dispatchToRoute(Request $request)    {        return $this->runRoute($request, $this->findRoute($request));
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php
                    
                    :200
                
            
        
        
                protected function dispatchToRouter()    {        return function ($request) {            $this->app->instance('request', $request);             return $this->router->dispatch($request);        };    }     /**     * Call the terminate method on any terminable middleware.     *     * @param  \Illuminate\Http\Request  $request     * @param  \Illuminate\Http\Response  $response     * @return void     */    public function terminate($request, $response)
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :169
                
            
        
        
                 */    protected function prepareDestination(Closure $destination)    {        return function ($passable) use ($destination) {            try {                return $destination($passable);            } catch (Throwable $e) {                return $this->handleException($passable, $e);            }        };    }     /**     * Get a Closure that represents a slice of the application onion.     *     * @return \Closure     */
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php
                    
                    :21
                
            
        
        
                 */    public function handle($request, Closure $next)    {        $this->clean($request);         return $next($request);    }     /**     * Clean the request's data.     *     * @param  \Illuminate\Http\Request  $request     * @return void     */    protected function clean($request)    {        $this->cleanParameterBag($request->query);
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ConvertEmptyStringsToNull.php
                    
                    :31
                
            
        
        
                        if ($callback($request)) {                return $next($request);            }        }         return parent::handle($request, $next);    }     /**     * Transform the given value.     *     * @param  string  $key     * @param  mixed  $value     * @return mixed     */    protected function transform($key, $value)    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php
                    
                    :21
                
            
        
        
                 */    public function handle($request, Closure $next)    {        $this->clean($request);         return $next($request);    }     /**     * Clean the request's data.     *     * @param  \Illuminate\Http\Request  $request     * @return void     */    protected function clean($request)    {        $this->cleanParameterBag($request->query);
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TrimStrings.php
                    
                    :51
                
            
        
        
                        if ($callback($request)) {                return $next($request);            }        }         return parent::handle($request, $next);    }     /**     * Transform the given value.     *     * @param  string  $key     * @param  mixed  $value     * @return mixed     */    protected function transform($key, $value)    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePostSize.php
                    
                    :27
                
            
        
        
                     if ($max > 0 && $request->server('CONTENT_LENGTH') > $max) {            throw new PostTooLargeException('The POST data is too large.');        }         return $next($request);    }     /**     * Determine the server 'post_max_size' as bytes.     *     * @return int     */    protected function getPostMaxSize()    {        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {            return (int) $postMaxSize;
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestsDuringMaintenance.php
                    
                    :109
                
            
        
        
                            null,                $this->getHeaders($data)            );        }         return $next($request);    }     /**     * Determine if the incoming request has a maintenance mode bypass cookie.     *     * @param  \Illuminate\Http\Request  $request     * @param  array  $data     * @return bool     */    protected function hasValidBypassCookie($request, array $data)    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Http/Middleware/HandleCors.php
                    
                    :48
                
            
        
        
                 * @return \Illuminate\Http\Response     */    public function handle($request, Closure $next)    {        if (! $this->hasMatchingPath($request)) {            return $next($request);        }         $this->cors->setOptions($this->container['config']->get('cors', []));         if ($this->cors->isPreflightRequest($request)) {            $response = $this->cors->handlePreflightRequest($request);             $this->cors->varyHeader($response, 'Access-Control-Request-Method');             return $response;        }
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php
                    
                    :58
                
            
        
        
                {        $request::setTrustedProxies([], $this->getTrustedHeaderNames());         $this->setTrustedProxyIpAddresses($request);         return $next($request);    }     /**     * Sets the trusted proxies on the request.     *     * @param  \Illuminate\Http\Request  $request     * @return void     */    protected function setTrustedProxyIpAddresses(Request $request)    {        $trustedIps = $this->proxies() ?: config('trustedproxy.proxies');
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/InvokeDeferredCallbacks.php
                    
                    :22
                
            
        
        
                 * @param  \Closure  $next     * @return \Symfony\Component\HttpFoundation\Response     */    public function handle(Request $request, Closure $next)    {        return $next($request);    }     /**     * Invoke the deferred callbacks.     *     * @param  \Illuminate\Http\Request  $request     * @param  \Symfony\Component\HttpFoundation\Response  $response     * @return void     */    public function terminate(Request $request, Response $response)    {
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Http/Middleware/ValidatePathEncoding.php
                    
                    :26
                
            
        
        
                     if (! mb_check_encoding($decodedPath, 'UTF-8')) {            throw new MalformedUrlException;        }         return $next($request);    }}
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :208
                
            
        
        
                                    // since the object we're given was already a fully instantiated object.                        $parameters = [$passable, $stack];                    }                     $carry = method_exists($pipe, $this->method)                        ? $pipe->{$this->method}(...$parameters)                        : $pipe(...$parameters);                     return $this->handleCarry($carry);                } catch (Throwable $e) {                    return $this->handleException($passable, $e);                }            };        };    }     /**
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php
                    
                    :126
                
            
        
        
                    $pipeline = array_reduce(            array_reverse($this->pipes()), $this->carry(), $this->prepareDestination($destination)        );         try {            return $pipeline($this->passable);        } finally {            if ($this->finally) {                ($this->finally)($this->passable);            }        }    }     /**     * Run the pipeline and return the result.     *     * @return mixed 
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php
                    
                    :175
                
            
        
        
                    $this->bootstrap();         return (new Pipeline($this->app))            ->send($request)            ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)            ->then($this->dispatchToRouter());    }     /**     * Bootstrap the application for HTTP requests.     *     * @return void     */    public function bootstrap()    {        if (! $this->app->hasBeenBootstrapped()) {            $this->app->bootstrapWith($this->bootstrappers());
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php
                    
                    :144
                
            
        
        
                    $this->requestStartedAt = Carbon::now();         try {            $request->enableHttpMethodParameterOverride();             $response = $this->sendRequestThroughRouter($request);        } catch (Throwable $e) {            $this->reportException($e);             $response = $this->renderException($request, $e);        }         $this->app['events']->dispatch(            new RequestHandled($request, $response)        );         return $response;
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/Application.php
                    
                    :1219
                
            
        
        
                 */    public function handleRequest(Request $request)    {        $kernel = $this->make(HttpKernelContract::class);         $response = $kernel->handle($request)->send();         $kernel->terminate($request, $response);    }     /**     * Handle the incoming Artisan command.     *     * @param  \Symfony\Component\Console\Input\InputInterface  $input     * @return int     */    public function handleCommand(InputInterface $input)
        
    
    
        
            
                

                                            public/index.php
                    
                    :20
                
            
        
        
             // Bootstrap Laravel and handle the request.../** @var Application $app */$app = require_once __DIR__.'/../bootstrap/app.php'; $app->handleRequest(Request::capture());
        
    
    
        
            
                

                                            vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
                    
                    :23
                
            
        
        
            $requestMethod = $_SERVER['REQUEST_METHOD'];$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT']; file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n"); require_once $publicPath.'/index.php';
        
    
        
    


                
    
        Request
    

    
        GET
        /home
    

    
        Headers
    

    
                    
                
                    host
                
                
                    tenant1.localhost:8000
                
            
                    
                
                    connection
                
                
                    keep-alive
                
            
                    
                
                    sec-ch-ua
                
                
                    "HeadlessChrome";v="149", "Chromium";v="149", "Not)A;Brand";v="24"
                
            
                    
                
                    sec-ch-ua-mobile
                
                
                    ?0
                
            
                    
                
                    sec-ch-ua-platform
                
                
                    "macOS"
                
            
                    
                
                    upgrade-insecure-requests
                
                
                    1
                
            
                    
                
                    user-agent
                
                
                    Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/149.0.7827.55 Safari/537.36
                
            
                    
                
                    accept-language
                
                
                    en-US
                
            
                    
                
                    accept
                
                
                    text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
                
            
                    
                
                    sec-fetch-site
                
                
                    none
                
            
                    
                
                    sec-fetch-mode
                
                
                    navigate
                
            
                    
                
                    sec-fetch-user
                
                
                    ?1
                
            
                    
                
                    sec-fetch-dest
                
                
                    document
                
            
                    
                
                    accept-encoding
                
                
                    gzip, deflate, br, zstd
                
            
            

    
        Body
    

    
        
            
                No body data
            
        
    



    
        Application
    

    
         Routing 
    

    
                    
                controller
                
                    App\Http\Controllers\Tenant\HomepageController@index
                
            
                    
                route name
                
                    home
                
            
                    
                middleware
                
                    web, Stancl\Tenancy\Middleware\InitializeTenancyByDomain, Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains, App\Http\Middleware\EnsureTenantIsActive
                
            
            

    
    
         Database Queries 
        
                    
    

    
                    
                
                    mysql
                    (1.14 ms)
                
                
                    select * from `tenants` where exists (select * from `domains` where `tenants`.`id` = `domains`.`tenant_id` and `domain` = 'tenant1.localhost') limit 1
                
            
                    
                
                    mysql
                    (0.37 ms)
                
                
                    select * from `domains` where `domains`.`tenant_id` in ('tenant1')
                
            
                    
                
                    mysql
                    (0.7 ms)
                
                
                    SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'tenant_tenant1'
                
            
                    
                
                    mysql
                    (0.35 ms)
                
                
                    select * from `tenants` where `tenants`.`id` = 'tenant1' limit 1
                
            
                    
                
                    tenant
                    (1.91 ms)
                
                
                    select * from `themes` where `is_active` = '1' order by `id` desc limit 1
                
            
                    
                
                    tenant
                    (2.14 ms)
                
                
                    update `themes` set `settings` = '{"colors":{"primary":"#111827","secondary":"#ffffff"},"header":{"enabled":true,"logo_text":"Aromniac","logo_image_url":"https:\/\/www.instagram.com\/aromaniac.tt\/","logo_position":"left","links":[{"label":"Shop","url":"\/home"},{"label":"Contact","url":"\/pages\/contact"},{"label":"Cart","url":"\/cart"}],"mobile_menu":true},"footer":{"enabled":true,"text":"Powered by your Hasan Marketing.","links":[{"label":"Shop","url":"\/home"},{"label":"Contact","url":"\/pages\/contact"}]}}', `themes`.`updated_at` = '2026-06-25 17:33:23' where `id` = 1
                
            
                    
                
                    tenant
                    (0.54 ms)
                
                
                    select * from `theme_pages` where (`theme_id` = 1 and `handle` = 'home') limit 1
                
            
                    
                
                    tenant
                    (0.38 ms)
                
                
                    select * from `theme_pages` where (`theme_id` = 1 and `handle` = 'contact') limit 1
                
            
                    
                
                    tenant
                    (0.32 ms)
                
                
                    select * from `themes` where `id` = 1 limit 1
                
            
                    
                
                    tenant
                    (0.41 ms)
                
                
                    select * from `theme_pages` where `theme_pages`.`theme_id` in (1)
                
            
                    
                
                    tenant
                    (0.38 ms)
                
                
                    select * from `theme_pages` where `theme_pages`.`theme_id` = 1 and `theme_pages`.`theme_id` is not null and `type` = 'home' limit 1
                
            
            

            
        
    

    

    


"

```

```yaml
- banner:
  - img
  - text: Internal Server Error
  - button:
    - img
- main:
  - text: ErrorException Undefined array key "name" GET tenant1.localhost:8000 PHP 8.4.22 — Laravel 12.7.2
  - button "Expand vendor frames":
    - text: Expand vendor frames
    - img
    - img
  - button "app/Services/Themes/ThemePageRenderer.php :26 array_map"
  - text: 2 vendor frames collapsed
  - button "app/Services/Themes/ThemePageRenderer.php :16 renderableSections"
  - button "App\\Http\\Controllers\\Tenant\\HomepageController :26 index"
  - text: 5 vendor frames collapsed
  - button "app/Http/Middleware/EnsureTenantIsActive.php :24 handle"
  - text: 5 vendor frames collapsed
  - button "App\\Http\\Middleware\\HandleAppearance :21 handle"
  - text: 1 vendor frame collapsed
  - button "app/Http/Middleware/EnsureCentralAdminAuthenticated.php :14 handle"
  - text: 48 vendor frames collapsed
  - button "public/index.php :20 require_once"
  - text: 1 vendor frame collapsed app/Services/Themes/ThemePageRenderer.php :26
  - code:
    - table:
      - rowgroup:
        - 'row "21 }"':
          - cell "21"
          - 'cell "}"'
        - row "22":
          - cell "22"
          - cell
        - row "23 return [":
          - cell "23"
          - cell "return ["
        - row "24 'id' => $section['id'] ?? ('section_' . uniqid()),":
          - cell "24"
          - cell "'id' => $section['id'] ?? ('section_' . uniqid()),"
        - row "25 'type' => $section['type'],":
          - cell "25"
          - cell "'type' => $section['type'],"
        - row "26 'name' => $schema['name'],":
          - cell "26"
          - cell "'name' => $schema['name'],"
        - row "27 'settings' => $this->settingsWithDefaults($schema, $section['settings'] ?? []),":
          - cell "27"
          - cell "'settings' => $this->settingsWithDefaults($schema, $section['settings'] ?? []),"
        - row "28 'blocks' => $this->visibleBlocks($section['blocks'] ?? []),":
          - cell "28"
          - cell "'blocks' => $this->visibleBlocks($section['blocks'] ?? []),"
        - row "29 ];":
          - cell "29"
          - cell "];"
        - 'row "30 })"':
          - cell "30"
          - 'cell "})"'
        - row "31 ->filter()":
          - cell "31"
          - cell "->filter()"
        - row "32 ->values()":
          - cell "32"
          - cell "->values()"
        - row "33 ->all();":
          - cell "33"
          - cell "->all();"
        - 'row "34 }"':
          - cell "34"
          - 'cell "}"'
        - row "35":
          - cell "35"
          - cell
        - 'row "36 public function homepageData(array $sections): array"':
          - cell "36"
          - 'cell "public function homepageData(array $sections): array"'
        - 'row "37 {"':
          - cell "37"
          - 'cell "{"'
  - text: Request GET /home Headers host
  - code: tenant1.localhost:8000
  - text: connection
  - code: keep-alive
  - text: sec-ch-ua
  - code: "\"HeadlessChrome\";v=\"149\", \"Chromium\";v=\"149\", \"Not)A;Brand\";v=\"24\""
  - text: sec-ch-ua-mobile
  - code: "?0"
  - text: sec-ch-ua-platform
  - code: "\"macOS\""
  - text: upgrade-insecure-requests
  - code: "1"
  - text: user-agent
  - code: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/149.0.7827.55 Safari/537.36
  - text: accept-language
  - code: en-US
  - text: accept
  - code: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
  - text: sec-fetch-site
  - code: none
  - text: sec-fetch-mode
  - code: navigate
  - text: sec-fetch-user
  - code: "?1"
  - text: sec-fetch-dest
  - code: document
  - text: accept-encoding
  - code: gzip, deflate, br, zstd
  - text: Body
  - code: No body data
  - text: Application Routing controller
  - code: App\Http\Controllers\Tenant\HomepageController@index
  - text: route name
  - code: home
  - text: middleware
  - code: web, Stancl\Tenancy\Middleware\InitializeTenancyByDomain, Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains, App\Http\Middleware\EnsureTenantIsActive
  - text: Database Queries mysql (1.14 ms)
  - code: "select * from `tenants` where exists (select * from `domains` where `tenants`.`id` = `domains`.`tenant_id` and `domain` = 'tenant1.localhost') limit 1"
  - text: mysql (0.37 ms)
  - code: "select * from `domains` where `domains`.`tenant_id` in ('tenant1')"
  - text: mysql (0.7 ms)
  - code: SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'tenant_tenant1'
  - text: mysql (0.35 ms)
  - code: "select * from `tenants` where `tenants`.`id` = 'tenant1' limit 1"
  - text: tenant (1.91 ms)
  - code: "select * from `themes` where `is_active` = '1' order by `id` desc limit 1"
  - text: tenant (2.14 ms)
  - code: "update `themes` set `settings` = '{\"colors\":{\"primary\":\"#111827\",\"secondary\":\"#ffffff\"},\"header\":{\"enabled\":true,\"logo_text\":\"Aromniac\",\"logo_image_url\":\"https:\\/\\/www.instagram.com\\/aromaniac.tt\\/\",\"logo_position\":\"left\",\"links\":[{\"label\":\"Shop\",\"url\":\"\\/home\"},{\"label\":\"Contact\",\"url\":\"\\/pages\\/contact\"},{\"label\":\"Cart\",\"url\":\"\\/cart\"}],\"mobile_menu\":true},\"footer\":{\"enabled\":true,\"text\":\"Powered by your Hasan Marketing.\",\"links\":[{\"label\":\"Shop\",\"url\":\"\\/home\"},{\"label\":\"Contact\",\"url\":\"\\/pages\\/contact\"}]}}', `themes`.`updated_at` = '2026-06-25 17:33:23' where `id` = 1"
  - text: tenant (0.54 ms)
  - code: "select * from `theme_pages` where (`theme_id` = 1 and `handle` = 'home') limit 1"
  - text: tenant (0.38 ms)
  - code: "select * from `theme_pages` where (`theme_id` = 1 and `handle` = 'contact') limit 1"
  - text: tenant (0.32 ms)
  - code: "select * from `themes` where `id` = 1 limit 1"
  - text: tenant (0.41 ms)
  - code: "select * from `theme_pages` where `theme_pages`.`theme_id` in (1)"
  - text: tenant (0.38 ms)
  - code: "select * from `theme_pages` where `theme_pages`.`theme_id` = 1 and `theme_pages`.`theme_id` is not null and `type` = 'home' limit 1"
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | 
  3   | const centralEmail = 'umarhmohammed04@gmail.com';
  4   | const centralPassword = 'password123';
  5   | 
  6   | async function expectPageHealthy(page) {
  7   |   await expect(page.locator('body')).toBeVisible();
> 8   |   await expect(page.locator('body')).not.toContainText('Server Error');
      |                                          ^ Error: expect(locator).not.toContainText(expected) failed
  9   |   await expect(page.locator('body')).not.toContainText('Method Not Allowed');
  10  |   await expect(page.locator('body')).not.toContainText('SQLSTATE');
  11  |   await expect(page.locator('body')).not.toContainText('Undefined variable');
  12  |   await expect(page.locator('body')).not.toContainText('Attempt to read property');
  13  | }
  14  | 
  15  | async function centralLogin(page) {
  16  |   await page.goto('http://localhost:8000/central/login');
  17  |   await page.fill('input[type="email"]', centralEmail);
  18  |   await page.fill('input[type="password"]', centralPassword);
  19  |   await page.click('button[type="submit"]');
  20  |   await page.waitForURL(/\/central$/);
  21  |   await expectPageHealthy(page);
  22  | }
  23  | 
  24  | test.describe('Central admin action audit', () => {
  25  |   test('central admin can open edit-style pages safely', async ({ page }) => {
  26  |     await centralLogin(page);
  27  | 
  28  |     const actionPages = [
  29  |       'http://localhost:8000/central/payment-settings',
  30  |       'http://localhost:8000/central/settings',
  31  |       'http://localhost:8000/central/tenants/tenant1',
  32  |       'http://localhost:8000/central/payouts/tenant1',
  33  |     ];
  34  | 
  35  |     for (const url of actionPages) {
  36  |       await page.goto(url);
  37  |       await expectPageHealthy(page);
  38  | 
  39  |       const buttons = page.locator('button');
  40  |       const count = await buttons.count();
  41  | 
  42  |       expect(count).toBeGreaterThan(0);
  43  |     }
  44  |   });
  45  | });
  46  | 
  47  | test.describe('Tenant admin action audit', () => {
  48  |   test('tenant manage pages expose usable action buttons/forms', async ({ page }) => {
  49  |     const pages = [
  50  |       'http://tenant1.localhost:8000/manage/billing',
  51  |       'http://tenant1.localhost:8000/manage/store-settings',
  52  |       'http://tenant1.localhost:8000/manage/payout-account',
  53  |       'http://tenant1.localhost:8000/manage/product',
  54  |       'http://tenant1.localhost:8000/manage/category',
  55  |       'http://tenant1.localhost:8000/manage/order',
  56  |     ];
  57  | 
  58  |     for (const url of pages) {
  59  |       await page.goto(url);
  60  |       await expectPageHealthy(page);
  61  | 
  62  |       const forms = await page.locator('form').count();
  63  |       const buttons = await page.locator('button').count();
  64  |       const links = await page.locator('a').count();
  65  | 
  66  |       expect(forms + buttons + links).toBeGreaterThan(0);
  67  |     }
  68  |   });
  69  | });
  70  | 
  71  | test.describe('Storefront customer action audit', () => {
  72  |   test('tenant storefront pages expose customer navigation/actions', async ({ page }) => {
  73  |     const pages = [
  74  |       'http://tenant1.localhost:8000/home',
  75  |       'http://tenant1.localhost:8000/cart',
  76  |     ];
  77  | 
  78  |     for (const url of pages) {
  79  |       await page.goto(url);
  80  |       await expectPageHealthy(page);
  81  | 
  82  |       const buttons = await page.locator('button').count();
  83  |       const links = await page.locator('a').count();
  84  | 
  85  |       expect(buttons + links).toBeGreaterThan(0);
  86  |     }
  87  |   });
  88  | });
  89  | 
  90  | test('storefront search and filters are usable when product grid exists, otherwise blank storefront stays healthy', async ({ page }) => {
  91  |   await page.goto('http://tenant1.localhost:8000/home');
  92  |   await expectPageHealthy(page);
  93  | 
  94  |   const searchInput = page.locator('input[type="search"]').first();
  95  |   const searchCount = await searchInput.count();
  96  | 
  97  |   if (searchCount > 0) {
  98  |     await searchInput.fill('audit');
  99  |     await page.keyboard.press('Enter');
  100 |     await expectPageHealthy(page);
  101 | 
  102 |     const sortSelect = page.locator('select').last();
  103 |     if (await sortSelect.count()) {
  104 |       await sortSelect.selectOption('price_low');
  105 |       await expectPageHealthy(page);
  106 |     }
  107 | 
  108 |     await expect(page.locator('body')).toContainText(/Products|No products found|Shop products/i);
```