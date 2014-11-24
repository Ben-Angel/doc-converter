<?php
namespace Userguide\Distributor\Models\Wordpress;

use WPAPI;
use WPAPI_Posts;

/**
 * Created by PhpStorm.
 * User: miko
 * Date: 11/13/14
 * Time: 11:47 AM
 */
class Posts extends WPAPI_Posts
{
    /**
     * @param array $filter
     * @see http://wp-api.org/#posts_retrieve-posts
     * @return mixed
     */
    public function getAllBy( array $filter = [ ] )
    {
        $uriFilter = $this->buildFilterPartUri( $filter );

        $response = $this->api->get( WPAPI::ROUTE_POSTS . $uriFilter );
        $posts    = json_decode( $response->body, true );
        foreach ($posts as &$post) {
            $post = new Post( $this->api, $post );
        }

        return $posts;
    }

    /**
     * @param array $filter
     *
     * @return string
     */
    protected function buildFilterPartUri( array $filter )
    {
        $uriFilter = '&filter[posts_per_page]=2000&page=1';
        foreach ($filter as $field => $terms) {
            $glue = '&filter[' . $field . ']=';
            if (count( $terms ) == 1) {
                $uriFilter .= $glue . $terms[0];
            } else {
                $uriFilter .= implode( $glue, $terms );
            }
        }

        return $uriFilter;
    }
}
