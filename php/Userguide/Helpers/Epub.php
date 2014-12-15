<?php
namespace Userguide\Helpers;


use Md2Epub\EBook;

class Epub extends EBook
{
    protected function exportBookFiles( $workDir, $filters = array() )
    {
        foreach ($this->files as $id => $file) {
            $src  = "{$this->home}/{$file['path']}";
            $dest = "$workDir/{$this->params['content_dir']}/{$file['path']}";
            $info = pathinfo($file['path']);
            $ext = $info['extension'];

            // if the file has a filter process then copy to destination directory, else copy only
            if (!empty($filters[$ext]) && is_callable($filters[$ext])) {
                // load file content and process it using the filter function
                if (file_exists($src) === false) {
                    throw new \Exception("Unable to load file '$src'");
                }
                $content = call_user_func($filters[$ext], $src);

                // you can use a custom template named after the file ID or the default page.xhtml template
                $template = 'page';
                if (file_exists("{$this->params['templates_dir']}/book/OEBPS/$id.xhtml")) {
                    $template = $id;
                }

                // compile template
                $tpl = $this->initTemplateEngine(
                    array(
                        'tpl_ext' => 'xhtml',
                        'path_replace' => false,
                        'auto_escape' => false,
                    )
                );

                $tpl->assign('BookTitle', $this->title);
                $tpl->assign('BookContent', $content);
                if (file_exists("{$this->home}/style.css")) {
                    $tpl->assign('BookStyle', 'style.css');
                }
                if (isset($this->description)) {
                    $tpl->assign('BookDescription', $this->description);
                }

                $content = $tpl->draw("book/OEBPS/$template", true);

                // save compiled file to the new destination
                $dest = "$workDir/{$this->params['content_dir']}/{$info['dirname']}/{$info['filename']}.xhtml";
                if (!is_dir(dirname($dest))) {
                    if (!mkdir(dirname($dest), 0777, true)) {
                        throw new \Exception("Unable to create path '" . dirname($dest) . "'");
                    }
                }

                if (file_put_contents($dest, $content) === false) {
                    throw new \Exception("Unable to create file '$dest'");
                }

                // update the files variable to reflect the change
                $this->files[$id] = array(
                    'type' => $this->mime($dest),
                );
                $this->files[$id]['path'] = ('.' != $info['dirname']) ? "{$info['dirname']}/" : '';
                $this->files[$id]['path'] .= "{$info['filename']}.xhtml";

                continue;
            }

            // Default behavior: copy the original file
            // Must check that the destination path exists
            if (!is_dir(dirname($dest))) {
                if (!mkdir(dirname($dest), 0777, true)) {
                    throw new \Exception("Unable to create path '" . dirname($dest) . "'");
                }
            }
            if (file_exists($src)) {
                copy($src, $dest);
            }
        }
    }


}