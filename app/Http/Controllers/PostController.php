<?php namespace App\Http\Controllers;

use App\Posts;
use App\User;
use Redirect;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class PostController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$posts = Posts::where('active',1)->orderBy('created_at','desc')->paginate(5);
		$title = 'Latest Posts';
		return view('home')->with('posts',$posts)->with('title',$title);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		// 
		return view('posts.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		
		$input['title'] = $request->input('title');
		$input['body'] = $request->input('body');
		$input['slug'] = str_slug($input['title']);
		$input['author_id'] = $request->user()->id;
		if($request->has('save'))
		{
			$input['active'] = 0;
			Posts::create( $input ); 
			return redirect('edit/'.$input['slug'])->with('message', 'Post saved successfully');			
		}			
		else {
			$input['active'] = 1;
			Posts::create( $input );
 			return redirect('edit/'.$input['slug'])->with('message', 'Post published successfully');
		} 		
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($slug)
	{
		$post = Posts::where('slug',$slug)->first();

		if($post)
		{
			if($post->active == '0')
				return redirect('/')->with('message','requested page not found');
			$comments = $post->comments;	
		}
		else 
		{
			return redirect('/')->with('message','requested page not found');
		}
		return view('posts.show')->with('post',$post)->with('comments',$comments);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit(Request $request,$slug)
	{
		$post = Posts::where('slug',$slug)->first();
		if($post && $request->user()->id == $post->author_id)
			return view('posts.edit')->with('post',$post);
		else 
		{
			return redirect('/');
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request)
	{
		//
		$post_id = $request->input('post_id');
		$post = Posts::find($post_id);
		if($post && $post->author_id == $request->user()->id)
		{
			$post->title = $request->input('title');
			$post->body = $request->input('body');
			$post->slug = str_slug($post->title);
			if($request->has('save'))
			{
				$post->active = 0;
				$post->save(); 
				return redirect('edit/'.$post->slug)->with('message', 'Post saved successfully');			
			}			
			else {
				$post->active = 1;
				$post->save();
	 			return redirect($post->slug)->with('message', 'Post updated successfully');
			} 
		}
		else
		{
			return redirect('/');
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy(Request $request, $id)
	{
		//
		$post = Posts::find($id);
		if($post && $post->author_id == $request->user()->id)
		{
			$post->delete();
			$data['message'] = 'Post deleted Successfully';
		}
		else 
		{
			$data['error'] = 'Invalid Operation';
		}
		
		return redirect('/')->with($data);
	}
	
	/*
	 * Display the posts of a particular user
	 * 
	 * @param int $id
	 * @return Response
	 */
	public function user_posts($id)
	{
		//
		$posts = Posts::where('author_id',$id)->where('active',1)->orderBy('created_at','desc')->paginate(5);
		$title = User::find($id)->name;
		return view('home')->with('posts',$posts)->with('title',$title);
	}

	public function user_posts_all(Request $request)
	{
		//
		$user = $request->user();
		$posts = Posts::where('author_id',$user->id)->orderBy('created_at','desc')->paginate(5);
		$title = $user->name;
		return view('home')->with('posts',$posts)->with('title',$title);
	}
	
	public function user_posts_draft(Request $request)
	{
		//
		$user = $request->user();
		$posts = Posts::where('author_id',$user->id)->where('active',0)->orderBy('created_at','desc')->paginate(5);
		$title = $user->name;
		return view('home')->with('posts',$posts)->with('title',$title);
	}
}
