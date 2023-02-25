<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Song;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WebPageCategoryController extends Controller
{
    //
    public $page;
    public $url;
    public function __construct(Request $request)
    {
        $this->page = $request->get('page');
        $this->url = "?page=";
    }

    public function loadView($songs, $title, $ogTitle, $ogDes){
        return view("webpage.categories.index",
            ["songs" => $songs, "page" => $this->page, "url" => $this->url,
                "og_title" => $ogTitle, "og_des" => $ogDes, "title" => $title]);
    }

    public function newestSongs()
    {
        $songs = Song::orderBy("id", "desc")->where("display", 1)->paginate(10);
        return $this->loadView($songs,
            "En Yeni Zil Sesleri",
            "En Yeni Zil Sesleri - Ücretsiz Telefon Zil Sesleri İndir ". Carbon::today()->year,
            "Cep telefonunuz için en yeni telefon zil sesleri koleksiyonu. Zilsesleritelefon.com'da tüm telefonlar için ücretsiz ve hızlı zil sesleri indirin.");
    }


    public function popularSongs()
    {
        $songs = Song::orderBy("listeners", "desc")->where("display", 1)->paginate(10);
        return $this->loadView($songs, "En Popüler Zil Sesleri",
            "En Popüler Zil Sesleri - En İyi Zil Sesleri Ücretsiz İndir",
            "Günümüzün popüler zil seslerini sentezleyin. Telefonunuz için çok çeşitli türlerde ve tüm mobil cihazlara uygun en iyi ve ücretsiz zil seslerini indirin",
        );
    }

    public function downloadSongs()
    {
        $songs = Song::orderBy("downloads", "desc")->where("display", 1)->paginate(10);
        return $this->loadView($songs, "En İyi 10 Zil Sesleri",
            "En Yeni Zil Sesleri " . Carbon::today()->year. "  - Düzenli olarak güncellenen En Yeni Zil Sesi bir listesini indirin",
            "Burada, düzenli olarak güncellenen binlerce En Yeni Zil Sesleri bir listesini sunuyoruz. Tüm zil sesi ücretsizdir ve yüksek kalitededir.",
        );
    }

    public function categorySongs($slug){
        // Slug Solve //
        $category = Category::where("category_slug", $slug)->where("display",1)->first();
        $song = Song::where("slug", $slug)->where("display",1)->first();
        $post = Post::where("slug", $slug)->where("display",1)->first();

        if ($category != null){ // has category

            $songs = Song::where("category_id", $category->id)->where("display", 1)->paginate(10);
            $title = "Klingeltöne $category->category_name | Beliebteste $category->category_name Klingelton Runterladen";
            $metaDes = "Die Sammlung der besten klingeltöne $category->category_name wird regelmäßig aktualisiert. Laden Sie kostenlose $category->category_name klingeltöne für Ihr Handy runterladen.";
            return $this->loadView($songs, $title, $category->meta_title, $metaDes);

            // return view
        } elseif ($song!= null){ // has Song

            $similarSongs = Song::where("category_id", $song->category_id)
                ->where("display", 1)
                ->where("id", "!=", $song->id)
                ->limit(12)->get();
            $currentListener = $song->listeners;
            Song::where("id", $song->id)->update(["listeners" => $currentListener+1]);
            return view("webpage.song.index",
                ["song" => $song, "similarSongs" => $similarSongs, "og_title" => $song->meta_title,
                    "og_des" => $song->meta_description]);

        } elseif ($post != null){ // has Post

            return view("webpage.post.index", ["post" => $post]);
        }
        else {
            abort("404");
        }
    }

    public function losMejores(){
        $songs  = Song::orderBy("downloads", "desc")->where("display", 1)->paginate(10);
        return $this->loadView($songs, "En İyi 10 Zil Sesleri",
            "Bugün En Çok İndirilen 10 Zil Koleksiyonu - Ücretsiz İndir",
            "Bugünün En Çok İndirilen Telefon Zil Sesleri Koleksiyonu - Bugünün En Çok İndirilen ve Dinlenen Telefon Zil Sesleri. Telefonunuz için ücretsiz indirme",
        );
    }
    public function search(Request $request, $search){
        $songs = Song::where('title', 'LIKE', "%$search%")->paginate(10);
        return $this->loadView($songs, "Search Results: $search", "You searched for $search - Descargar tono de llamada mp3 gratis para móvil Android e iOS 2022",
            "");
    }
}
