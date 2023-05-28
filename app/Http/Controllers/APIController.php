<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class APIController extends Controller
{

    public function profile(Request $request){
        try {
            $user = User::find($request['uid']);
            $response = [
                'status'=>true,
                'data'=>$user,
                'msg'=>'User Account fetched Successfully',
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status'=>false,
                'msg'=>'Unable to process your request, please try again later',
            ];
            return response()->json($response, 200);
        }
    }

    public function registerUser(Request $request){
        try {
            $user = new User();
            $user->name  = strip_tags($request['username']);
            $user->email = strip_tags($request['email']);
            $user->password = Hash::make($request['password']);
            $user->save();
            $response = [
                'status'=>true,
                'data'=>$user,
                'msg'=>'User Account Created Successfully',
                
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            $response = [
                'status'=>false,
                'msg'=>'Accont exists already. Please Login',
            ];
            return response()->json($response, 200);
        }
    }
    public function login(Request $request){
        try {
            $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $response = [
                    'status'=>true,
                    'data'=>$user,

                    'msg'=>'User Account fetched Successfully',
                ];
            }else {
                $response = [
                    'status'=>false,
                    'msg'=>'Invalid Email or Password please try again.',
                ];
            }
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status'=>false,
                'msg'=>'Unable to process your request, please try again later.',
            ];
            return response()->json($response, 200);
        }
    }
    /**
     * Function to retreive news artivles from various sources
     * Route:news
     * Route Method:GET
     */
    public function getNewsFromService1(Request $request){
        try {
            //serch keyword
            $query = $request['query'];
            //user id
            $user = User::find($request['uid']);
            $news = [];
            $authors = [];
            $sources = [];
            //format date to support fetching news by give date
            $originalFormat = 'Y-m-d';
            $newFormat = 'Y-d-m';
            $odate = isset($request['date']) ? $request['date'] : date('Y-m-d');
            $page = isset($request['page']) ? $request['page'] : 1;
            $date = Carbon::createFromFormat($originalFormat, $odate);
            $newDate = $date->format($newFormat);
            //default categories for newsapi.org
            $categories = ['business','entertainment','general','health','science','sports','technology'];
            
            // if($request['uid'] == 0){
            //     $url1 = "https://newsapi.org/v2/top-headlines?q=".$query."&country=us&from=".$newDate."&apiKey=".env('NEWS_API_ORG_KEY');
            //     $url2 = "https://content.guardianapis.com/search?q=".$query."&api-key=".env('GUARDIAN_API_KEY').'&show-fields=thumbnail,publication';
            // }else{
            //     $newssources = implode(',', json_decode ($user->preferred_sources));
            //     $newsCategories = implode('|', json_decode ($user->preferred_categories));
            //     $url1 = "https://newsapi.org/v2/everything?q=".$query."&from=".$newDate."&sources=".$newssources."&apiKey=".env('NEWS_API_ORG_KEY');
            //     if( $newsCategories !=""){
            //         $url2 = "https://content.guardianapis.com/search?q=".$query."&from-date=".$odate."&section=".$newsCategories."&api-key=".env('GUARDIAN_API_KEY').'&show-fields=thumbnail,publication';
            //     }else{
            //         $url2 = "https://content.guardianapis.com/search?q=".$query."&from-date=".$odate."&api-key=".env('GUARDIAN_API_KEY').'&show-fields=thumbnail,publication';
            //     }
            // }

            //construct urls based on request parameters
            $url1 = "https://newsapi.org/v2/top-headlines?q=".$query."&from=".$newDate."&apiKey=".env('NEWS_API_ORG_KEY')."&page=".$page."&sortBy=popularity&country=us";
            $url2 = "https://content.guardianapis.com/search?q=".$query."&from-date=".$odate."&api-key=".env('GUARDIAN_API_KEY')."&page=".$page."&show-fields=thumbnail,publication";
            $url3 = "https://api.nytimes.com/svc/search/v2/articlesearch.json?q=".$query."&pub_date=".$odate."&api-key=".env('NEW_YORK_TIMES_KEY')."&page=".$page;
            //make CURL call
            $newsResponse1 = Http::get($url1);
            $newsResponse2 = Http::get($url2);
            $newsResponse3 = Http::get($url3);
            //collect responce
            $items1 = $newsResponse1->collect();
            $items2 = $newsResponse2->collect();
            $items3 = $newsResponse3->collect();
            //assign news
            $items1 = $items1['articles'];
            $items2 = $items2['response']['results'];
            $items3 = $items3['response']['docs'];
            //format results from newsapi.org
            foreach ($items1 as $item) {
                $prefs = [$item['author'],'General',$item['source']['id']];
                $newsItem = [
                    'author'      => $item['author'],
                    'title'       => $item['title'],
                    'description' => $item['description'],
                    'urlToImage'  => $item['urlToImage'],
                    'publishedAt' => $item['publishedAt'],
                    'content'     => $item['content'],
                    'source'      => $item['source']['id'],
                    'category'    => 'General',
                    'url'         => $item['url'],
                    'data_source' => 'newsapi.org',
                    'weight'      => $this->addWeight($prefs, $user),
                ];
                array_push($authors, $item['author']);
                array_push($sources, $item['source']['id']);
                array_push($news, $newsItem);
            }
             //format results from The Guardian
            foreach ($items2 as $item) {
                $prefs = ['The Guardian', $item['sectionId'], $item['fields']['publication']];
                $newsItem = [
                    'author'      => 'The Guardian',
                    'title'       => $item['webTitle'],
                    'description' => "",
                    'urlToImage'  => $item['fields']['thumbnail'],
                    'publishedAt' => $item['webPublicationDate'],
                    'content'     => "",
                    'source'      => $item['fields']['publication'],
                    'category'    => $item['sectionId'],
                    'url'         => $item['webUrl'],
                    'data_source' => 'theguardian.com',
                    'weight'      => $this->addWeight($prefs, $user),
                ];
                array_push($authors, 'The Guardian');
                array_push($sources, $item['fields']['publication']);
                array_push($categories, $item['sectionId']);
                array_push($news, $newsItem);
            }
             //format results from The New York Times
             foreach ($items3 as $item) {
                $prefs = [$item['byline']['original'], $item['section_name'], $item['source']];
                $newsItem = [
                    'author'      => $item['byline']['original'],
                    'title'       => $item['snippet'],
                    'description' => $item['lead_paragraph'],
                    'urlToImage'  =>  count($item['multimedia']) != 0 ? 'https://static01.nyt.com/'.$item['multimedia'][0]['url'] :'https://source.unsplash.com/random',
                    'publishedAt' => $item['pub_date'],
                    'content'     => $item['lead_paragraph'],
                    'source'      => $item['source'],
                    'category'    => $item['section_name'],
                    'url'         => $item['web_url'],
                    'data_source' => 'nytimes.com',
                    'weight'      => $this->addWeight($prefs, $user),
                ];
                array_push($authors, $item['byline']['original']);
                array_push($sources, $item['source']);
                array_push($categories, $item['section_name']);
                array_push($news, $newsItem);
            }

            //sort news by weight : 1 - preferred, 0 - general
            if ($request['uid'] !== 0) {
                $news = collect($news)->sortByDesc('weight')->values()->all();;
            }
            $response = [
                'msg'        => 'News fetched Successfully',
                'status'     => true,
                'data'       => $news,
                'profile'    => $user,
                'authors'    => $authors,
                'sources'    => $sources,
                'categories' => $categories,
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status'=>false,
                'msg'=>$e->getMessage().$e->getLine().$e->getFile(),
            ];
            return response()->json($response, 200);
        }
    }
    /**
     * Function to add weight to new article, based on user preferences
     */
    public function addWeight($prefs, $user)
    {
        // $prefs[0] - author
        // $prefs[1] - category
        // $prefs[2] - source
        if (!isset($user)) {
         return 0;
        } else {
            $sources    = json_decode($user->preferred_sources);
            $categories = json_decode($user->preferred_categories);
            $authors    = json_decode($user->preferred_authors);
            if (in_array($prefs[0], $authors) || in_array($prefs[1], $categories)|| in_array($prefs[2], $sources)) {
                return 1;
            }else {
                return 0;
            }
        }
    }
    /**
     * Function to save and update user preferences like sources, authors and categories
     * Route:add-to-preferences
     * Route Method:POST
     */
    public function addToPreferences(Request $request) {
        try {
            $user = User::find($request['uid']);
            switch ($request['type']) {
                case 'author':
                    $user->preferred_authors = $request['list'];
                    break;
                case 'source':
                    $user->preferred_sources = $request['list'];
                    break;
                case 'category':
                    $user->preferred_categories = $request['list'];
                    break;
                default:
                    break;
            }
            $user->save();
            $response = [
                'status'=>true,
                'data'=>$user,
                'msg'=>'Added to Prefrences',
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status'=>false,
                'msg'=>'Unable to process your request, please try again later.',
            ];
            return response()->json($response, 200);
        }
    }

    /**
     * Function to update user name, and password
     * Route:update/account
     * Route Method:POST
     */
    public function updateAccount(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $user->password = Hash::make($request['newPassword']);
                $user->name = $request['name'];
                $user->save();
                $response = [
                    'status'=>true,
                    'data'=>$user,
                    'msg'=>'User Account updated Successfully',
                ];
            }else {
                $response = [
                    'status'=>false,
                    'msg'=>'Invalid Email or Password please try again.',
                ];
            }
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'status'=>false,
                'msg'=>$e->getMessage(),
            ];
            return response()->json($response, 200);
        }
    }
}
