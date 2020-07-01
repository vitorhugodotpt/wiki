<?php

namespace Pdmfc\Wiki\Http\Controllers;

use Pdmfc\Wiki\DocumentationRepository;

class SearchController extends Controller
{
    /**
     * @var DocumentationRepository
     */
    protected $documentationRepository;

    /**
     * SearchController constructor.
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
     * Get the index of a given version.
     *
     * @param $version
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke($version)
    {
        $this->authorizeAccessSearch($version);

        return response()->json(
            $this->documentationRepository->search($version)
        );
    }

    /**
     * @param $version
     */
    protected function authorizeAccessSearch($version)
    {
        abort_if(
            $this->documentationRepository->isNotPublishedVersion($version)
            ||
            config('wiki.search.default') != 'internal'
            ||
            ! config('wiki.search.enabled')
        , 403);
    }
}
