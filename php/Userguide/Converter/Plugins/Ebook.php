<?php
namespace Userguide\Converter\Plugins;

use Jig\Utils\FsUtils;
use Userguide\Converter\PluginAbstract;
use Userguide\Converter\PluginInterface;
use Userguide\Helpers\Epub;
use Userguide\Helpers\Indexer;

class Ebook extends PluginAbstract implements PluginInterface

{
    protected $baseTmpDir;
    protected $fileListing;

    /**
     * Run the actual conversion from Markdown to target format
     *
     * @param array $fileListing
     *
     * @return mixed
     */
    public function runConversion( array $fileListing )
    {

        $this
            ->init( $fileListing )
            ->prepareMdStructure()
            ->prepareMap()
            ->prepareIndex()
            ->makeEpubBook()
            ->makeFB2BookFromEpub();
    }

    protected function prepareMdStructure()
    {

        FsUtils::mkDir( $this->baseTmpDir );

        foreach ($this->fileListing as $nodeId => $fileName) {
            $fullTargetPath = $this->baseTmpDir .'/'. explode('.',$this->indexer->getMetaTree()[$nodeId]['flat'])[0] . '.md';
            copy( $fileName, $fullTargetPath );
        }

        copy(
            $this->paths['base'] . $this->paths['trees'] . '/' . Indexer::FILE_MAP_LINKS_FLAT,
            $this->baseTmpDir . '/'. Indexer::FILE_MAP_LINKS_FLAT
        );

        return $this;
    }

    private function makeEpubBook()
    {
        $ebook = new Epub($this->baseTmpDir );

        $workingDir = sys_get_temp_dir() . uniqid( 'tao_' );

        if (is_dir( $workingDir )) {
            FsUtils::rmDir( $workingDir );
        }
        FsUtils::mkDir( $workingDir );

        $ebook->makeEpub(
            array(
                'out_file'      => $this->getBaseOutputPath() . '/book.epub',
                'working_dir'   => $workingDir,
                'templates_dir' =>  $this->getResourceDir() ,
                'filters'       => array(
                    'md' => function ( $src ) {
                        $outputPath = $this->getOutputPath(dirname($src));
                        system(sprintf('%s -f markdown -t html %s > %s',
                            $this->options['bin'],
                            $src . ' ' . $this->paths['base'] . $this->paths['trees'] . '/' . Indexer::FILE_MAP_LINKS_FLAT,
                            $outputPath . '/' . basename($src, '.md') . '.xhtml'), $retVal);
                        return file_get_contents($outputPath . '/' . basename($src, '.md') . '.xhtml');
                    }
                )
            )
        );

        return $this;
    }

    private function init( array $fileListing )
    {
        $this->baseTmpDir = sys_get_temp_dir() . uniqid( 'tao_' );

        $outputPath = $this->getBaseOutputPath() . '/tmp';
        FsUtils::mkDir( $outputPath );

        $this->fileListing = $fileListing;

        return $this;
    }

    private function prepareMap()
    {

        //we can change any meta structure for book
        copy(
            $this->getResourceDir() .  '/book.json',
            $this->baseTmpDir . '/book.json'
        );

        return $this;
    }

    private function prepareIndex()
    {
        //generates index file
        return $this;
    }

    private function makeFB2BookFromEpub()
    {
        $outputPath = $this->getBaseOutputPath();

        $sourceFile =  $outputPath . '/book.epub';

        system(sprintf('%s -f markdown -t fb2 %s > %s',
            $this->options['bin'],
            $sourceFile,
            $outputPath . '/' . basename($sourceFile, '.epub') . '.fb2'), $retVal);

        return $this;
    }

}