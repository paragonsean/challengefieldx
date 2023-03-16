<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\TicketTag;
use FluentSupport\Framework\Request\Request;

/**
 *  TicketTagsController class for REST API
 * This class is responsible for all interactions related to ticket
 * @package FluentSupport\App\Http\Controllers
 *
 * @version 1.0.0
 */

class TicketTagsController extends Controller
{

    /**
     * index method will return the list of ticket tag exist in database
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $tags = TicketTag::orderBy('id', 'DESC')->searchBy($request->get('search'))->paginate();

        foreach ($tags as $tag) {
            $tag->count = $tag->tickets()->count();
        }

        return [
            'tags' => $tags
        ];
    }

    /**
     * get method will return the list of ticket tag by tag id
     * @param Request $request
     * @param $tagId
     * @return array
     */
    public function get(Request $request, $tagId)
    {
        $product = TicketTag::findOrFail($tagId);
        return [
            'tags' => $product
        ];
    }

    /**
     * Create method will create new tag
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {
        $data = $request->all();//Get all data from request

        //Check data validity
        $this->validate($data, [
            'title' => 'required'
        ]);

        $data = wp_unslash($data);
        $product = TicketTag::create($data);

        return [
            'message' => __('Tag has been successfully created', 'fluent-support'),
            'tag' => $product
        ];
    }

    /**
     * Update method will update existing tag by tag id
     * @param Request $request
     * @param $tagId
     * @return array
     */
    public function update(Request $request, $tagId)
    {
        $data = $request->all();//Get all data from request

        //Check data validity
        $this->validate($data, [
            'title' => 'required'
        ]);

        $product = TicketTag::findOrFail($tagId);
        $product->fill($data);
        $product->save();

        return [
            'message' => __('Tag has been updated', 'fluent-support'),
            'tag' => TicketTag::find($tagId)
        ];
    }

    /**
     * delete method will delete tag by tag id
     * @param Request $request
     * @param $tagId
     * @return array
     */
    public function delete(Request $request, $tagId)
    {
        TicketTag::where('id', $tagId)
            ->delete();

        return [
            'message' => __('Tag has been deleted', 'fluent-support')
        ];
    }

    /**
     * getOptions method will fetch all tag for ticket and return
     * @return array
     */
    public function getOptions()
    {
        return [
            'option_key' => 'ticket_tags',
            'options' => TicketTag::select('id', 'title')->orderBy('title', 'ASC')->get()
        ];
    }

}
