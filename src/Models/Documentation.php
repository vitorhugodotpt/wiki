<?php

namespace Pdmfc\Wiki\Models;

use Pdmfc\Wiki\Cache;
use Illuminate\Filesystem\Filesystem;
use Pdmfc\Wiki\Traits\Indexable;
use Pdmfc\Wiki\Traits\HasBladeParser;
use Pdmfc\Wiki\Traits\HasMarkdownParser;

class Documentation
{
    use HasMarkdownParser, HasBladeParser, Indexable;

    /**
     * The filesystem implementation.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * The cache implementation.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Create a new documentation instance.
     *
     * @param Filesystem $files
     * @param Cache $cache
     */
    public function __construct(Filesystem $files, Cache $cache)
    {
        $this->files = $files;
        $this->cache = $cache;
    }

    /**
     * Get the documentation index page.
     *
     * @param  string  $version
     * @return string
     */
    public function getIndex($version, $role)
    {
        return $this->cache->remember(function() use($version, $role) {
            $path = base_path(config('wiki.docs.path').'/'.$version.'/menu.'.$role.'.md');

            if ($this->files->exists($path)) {
                $parsedContent = $this->parse($this->files->get($path));

                return $this->replaceLinks($version, $role, $parsedContent);
            }

            return null;
        }, 'larecipe.docs.'.$version.'.'.$role.'.index');
    }

    /**
     * Get the given documentation page.
     *
     * @param $version
     * @param $role
     * @param $page
     * @param array $data
     * @return mixed
     */
    public function get($version, $role, $page, $data = [])
    {
        return $this->cache->remember(function() use($version, $role, $page, $data) {
            $file = $page;
            if($page === 'index') {
                $file = $page.'.'.$role;
            }
            $path = base_path(config('wiki.docs.path').'/'.$version.'/'.$file.'.md');

            if ($this->files->exists($path)) {
                $parsedContent = $this->parse($this->files->get($path));

                $parsedContent = $this->replaceLinks($version, $role, $parsedContent);

                return $this->renderBlade($parsedContent, $data);
            }

            return null;
        }, 'larecipe.docs.'.$version.'.'.$page);
    }

    /**
     * Replace the version and route placeholders.
     *
     * @param  string  $version
     * @param  string  $role
     * @param  string  $content
     * @return string
     */
    public static function replaceLinks($version, $role, $content)
    {
        $content = str_replace('{{link_menu}}', '{{route}}/{{version}}/{{role}}', $content);

        $content = str_replace('{{version}}', $version, $content);

        $content = str_replace('{{role}}', $role, $content);

        $content = str_replace('{{image_folder}}', config('app.url') . '/' . config('wiki.docs.image_folder'), $content);

        $content = str_replace('{{route}}', trim(config('wiki.docs.route'), '/'), $content);

        $content = str_replace('"#', '"'.request()->getRequestUri().'#', $content);

        return $content;
    }

    /**
     * Check if the given section exists.
     *
     * @param  string  $version
     * @param  string  $page
     * @return bool
     */
    public function sectionExists($version, $page)
    {
        return $this->files->exists(
            base_path(config('wiki.docs.path').'/'.$version.'/'.$page.'.md')
        );
    }
}
