<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\Posts;

use Illuminate\Http\Request;

class UserController extends Controller {

		/*
	 * Display the posts of a particular user
	 * 
	 * @param int $id
	 * @return Response
	 */
	public function user_posts($id)
	{
		//
		$posts = Posts::where('author_id',$id)->where('active','1')->orderBy('created_at','desc')->paginate(5);
		$title = User::find($id)->name;
		return view('home')->withPosts($posts)->withTitle($title);
	}

	public function user_posts_all(Request $request)
	{
		//
		$user = $request->user();
		$posts = Posts::where('author_id',$user->id)->orderBy('created_at','desc')->paginate(5);
		$title = $user->name;
		return view('home')->withPosts($posts)->withTitle($title);
	}
	
	public function user_posts_draft(Request $request)
	{
		//
		$user = $request->user();
		$posts = Posts::where('author_id',$user->id)->where('active','0')->orderBy('created_at','desc')->paginate(5);
		$title = $user->name;
		return view('home')->withPosts($posts)->withTitle($title);
	}

	/**
	 * profile for user
	 */
	public function profile(Request $request, $id) 
	{
		$data['user'] = User::find($id);
		if (!$data['user'])
			return redirect('/');

		if ($request -> user() && $data['user'] -> id == $request -> user() -> id) {
			$data['author'] = true;
		} else {
			$data['author'] = null;
		}
		$data['comments_count'] = $data['user'] -> comments -> count();
		$data['posts_count'] = $data['user'] -> posts -> count();
		$data['posts_active_count'] = $data['user'] -> posts -> where('active', '1') -> count();
		$data['posts_draft_count'] = $data['posts_count'] - $data['posts_active_count'];
		$data['latest_posts'] = $data['user'] -> posts -> where('active', '1') -> take(5);
		$data['latest_comments'] = $data['user'] -> comments -> take(5);
		return view('admin.profile', $data);
	}

}

