<?php

namespace App\Http\Controllers;

use App\Http\Resources\NewsletterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Newsletter;

class NewsController extends Controller
{

    /**
     * Show all subscribers data.
     *
     * @return Response
     */
    public function showAllNewsletters()
    {
        return response()->json(NewsletterResource::collection(Newsletter::all()), 200);
    }

    /**
     * Show the newsletter data by the given id.
     *
     * @param  int  $id
     * @return Response
     */
    public function showOneNewsletter($id)
    {
        return response()->json(NewsletterResource::collection(Newsletter::find($id)), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = $this->validate($request, [
            'email' => 'required|email|max:100|distinc|unique:newsletters',
            'name' => 'required|string'
        ]);
        $newsletter = new Newsletter();
        $newsletter->email = $request->input('email');
        $newsletter->name = strtoupper($request->input('name'));
        $newsletter->code = Str::random(40);
        if($newsletter->save()) {
            // @TODO - Send E-Mail about subscribing newsletter.
            return response(["status" => true, "message" => "Thank you for registering for the newsletter!"], 201);
        }else{
            return response(["status" => false, "message" => $validator], 422);
        }
    }

    /**
     * Updates an newsletter data by given ID
     *
     * @param  int      $id
     * @param  Request  $request
     * @return Response
     */
    public function update($id, Request $request)
    {
        $validator = $this->validate($request, [
            'email' => 'email|max:100|distinc|unique:newsletters',
            'name' => 'string',
            'code' => 'string|min:40|max:40'
        ]);

        $newsletter = Newsletter::findOrFail($id);
        $newsletter->update($request->all());

        return response(["status" => true, "message" => "Updated successfully"], 200);
    }

    /**
     * Unsubscribe a user from Newsletter 
     *
     * @param  Request  $request
     * @return Response
     */
    public function destroy(Request $request)
    {
        $validator = $this->validate($request, [
            'code' => 'required|string|min:40|max:40'
        ]);

        $newsletter = Newsletter::where('code', $request->input('code'))->firstOrFail();
        // @TODO - Send E-Mail about deleting record
        if($newsletter->delete()) {
            return response(["status" => true, "message" => "You have unsubscribed from the newsletter."], 200);
        }else{
            return response(["status" => false, "message" => sprintf("Error occured! Validation errors: %s", $validator)], 422);
        }
        
    }
    //
}
