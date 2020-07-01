@if(config('wiki.search.enabled'))
    @if(config('wiki.search.default') == 'algolia')
        <algolia-search-box
            v-if="searchBox"
            @close="searchBox = false"
            algolia-key="{{ config('wiki.search.engines.algolia.key') }}"
            algolia-index="{{ config('wiki.search.engines.algolia.index') }}"
            version="{{ $currentVersion }}"
        ></algolia-search-box>
    @elseif(config('wiki.search.default') == 'internal')
        <internal-search-box
            v-if="searchBox"
            @close="searchBox = false"
            version-url="{{ route('larecipe.show', ['version' => $currentVersion]) }}"
            search-url="{{ route('larecipe.search', ['version' => $currentVersion]) }}"
        ></internal-search-box>
    @endif
@endif
