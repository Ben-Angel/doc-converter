<?php
/**
 * Created by PhpStorm.
 * User: miko
 * Date: 11/14/14
 * Time: 1:27 PM
 */
namespace Userguide\Distributor\Plugins;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Userguide\Distributor\Models\Wordpress\Posts;
use Userguide\Distributor\PluginAbstract;
use Userguide\Distributor\PluginInterface;
use WPAPI;


class Wordpress extends PluginAbstract implements PluginInterface
{
    const status = 'publish';

    public function execute()
    {

        $finder = new Finder();
        $finder->files()->name( '*.html' )->in( $this->paths['source'] . DIRECTORY_SEPARATOR );

        $api = new WPAPI( $this->params['uri'], $this->params['login'], $this->params['password'] );

        $posts = new Posts( $api );

//        clean up
        /** @var WPAPI_Post $post */
        foreach ($posts->getAllBy( [ 'tag' => $this->getTags() ] ) as $post) {
            $post->delete( true );
        }

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {

            $posts->create(
                [
                    'title'        => $this->getPostTitle( $file ),
                    'content_raw'  => $file->getContents(),
                    'status'       => self::status,
                    'x-tags'       => $this->getTags(),
                    'x-categories' => $this->getPostCategories( $file ),
                ]

            );
        }
    }

    protected function getTags()
    {
        return $this->params['tags'];
    }


    /**
     * @TODO  it's temporary  solution, must retrieve info from tree
     *
     * @param SplFileInfo $file
     *
     * @return string
     */
    public function getPostTitle( SplFileInfo $file )
    {
        $DOM = new \DOMDocument();
        if (strlen( $file->getContents() ) > 10) {
            $DOM->loadHTML( $file->getContents() );

            return $DOM->getElementsByTagName( 'h1' )->item( 0 )->textContent;
        }

        return explode( '.', $file->getBasename() )[0];
    }

    /**
     * @TODO it's temporary solution, must retrieve info from tree
     *
     * @param SplFileInfo $file
     *
     * @return array
     */
    public function getPostCategories( SplFileInfo $file )
    {
        return array_merge(
            explode( DIRECTORY_SEPARATOR, $file->getRelativePath() ),
            [ 'userguide' ]
        );
    }
}