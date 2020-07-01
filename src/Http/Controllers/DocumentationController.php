<?php

namespace Pdmfc\Wiki\Http\Controllers;

use App\Permission;
use App\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Pdmfc\Wiki\DocumentationRepository;

class DocumentationController extends Controller
{
    /**
     * @var DocumentationRepository
     */
    protected $documentationRepository;

    /**
     * DocumentationController constructor.
     * @param DocumentationRepository $documentationRepository
     */
    public function __construct(DocumentationRepository $documentationRepository)
    {
        $this->documentationRepository = $documentationRepository;

        if (config('wiki.settings.auth')) {
            $this->middleware(['auth']);
        }else{
            if(config('wiki.settings.middleware')){
                $this->middleware(config('wiki.settings.middleware'));
            }
        }
    }

    /**
     * Redirect the index page of docs to the default version.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect()->route(
            'wiki.show',
            [
                'version' => config('wiki.versions.default'),
                'role'  => Str::slug(auth()->user()->role->name),
                'page' => config('wiki.docs.landing')
            ]
        );
    }

    /**
     * Show a documentation page.
     *
     * @param $version
     * @param $role
     * @param null $page
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($version, $role, $page = null)
    {
        $documentation = $this->documentationRepository->get($version, $role, $page);

        if (Gate::has('viewLarecipe')) {
            $this->authorize('viewLarecipe', $documentation);
        }


        if ($this->documentationRepository->isNotPublishedVersion($version)) {
            return redirect()->route(
                'wiki.show',
                [
                    'version' => config('wiki.versions.default'),
                    'role'  => Str::slug(auth()->user()->role->name),
                    'page' => config('wiki.docs.landing')
                ]
            );
        }

        $roles = Permission::where('is_document', 1)
            ->where('role_id', auth()->user()->role->id)
            ->where('view', 1)
            ->pluck('resource')
            ->toArray();

        $pagePermission = $this->hasPermissionToSee($documentation->index, $version, $role);

        if($page !== 'index' && !in_array($page, $pagePermission, true)) {
            return redirect()->route(
                'wiki.show',
                [
                    'version' => config('wiki.versions.default'),
                    'role'  => Str::slug(auth()->user()->role->name),
                    'page' => config('wiki.docs.landing')
                ]
            );
        }

        if(!in_array($role, $roles, true)) {
            if($role !== Str::slug(auth()->user()->role->name)) {
                return redirect('dashboards/main');
            }

            return redirect()->route(
                'wiki.show',
                [
                    'version' => config('wiki.versions.default'),
                    'role'  => Str::slug(auth()->user()->role->name),
                    'page' => config('wiki.docs.landing')
                ]
            );
        }




        return response()->view('wiki::docs', [
            'title'          => $documentation->title,
            'index'          => $documentation->index,
            'content'        => $documentation->content,
            'currentVersion' => $version,
            'versions'       => $documentation->publishedVersions,
            'currentSection' => $documentation->currentSection,
            'canonical'      => $documentation->canonical,
            'roles'          => $roles,
            'currentRole'    => ucwords(str_replace('_', ' ', $role))
        ], $documentation->statusCode);
    }

    private function hasPermissionToSee($index, $version, $role)
    {
        preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $index, $result);

        if(!isset($result['href'])) {
            return null;
        }

        return collect($result['href'])->map(static function($row) use ($version, $role) {
            return str_replace('/wiki/'.$version.'/'.$role, '', $row);
        })->map(static function($row) {
            return str_replace('/', '', $row);
        })->filter(static function($row) {
            return !empty($row);
        })->toArray();
    }

}
