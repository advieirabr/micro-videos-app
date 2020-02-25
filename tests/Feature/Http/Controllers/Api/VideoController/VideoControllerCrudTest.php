<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;


use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Tests\Traits\TestSaves;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves, TestUploads;

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => '',

        ];

        $this->assertInvalidationStoreAction($data, 'required');
        $this->assertInvalidationUpdateAction($data, 'required');

    }

    public function testInvalidationMax()
    {
        $data = [
            'title' => str_repeat('a', 256),

        ];

        $this->assertInvalidationStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationUpdateAction($data, 'max.string', ['max' => 255]);

    }

    public function testInvalidationInteger()
    {
        $data = [
            'duration' => 'a',

        ];

        $this->assertInvalidationStoreAction($data, 'integer');
        $this->assertInvalidationUpdateAction($data, 'integer');

    }

    public function testInvalidationYearLaunchedField()
    {
        $data = [
            'year_launched' => 'a',

        ];

        $this->assertInvalidationStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationUpdateAction($data, 'date_format', ['format' => 'Y']);

    }

    public function testInvalidationCategoriesIdField()
    {
        $data = [
            'categories_id' => 'a',

        ];

        $this->assertInvalidationStoreAction($data, 'array');
        $this->assertInvalidationUpdateAction($data, 'array');


        $data = [
            'categories_id' => [100],

        ];

        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();

        $data = [
            'categories_id' => [$category->id],

        ];

        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');

        $category = factory(Genre::class)->create();
        $category->delete();

        $data = [
            'genres_id' => [$category->id],

        ];

        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');

    }

    public function testInvalidationGenresIdField()
    {
        $data = [
            'genres_id' => 'a',

        ];

        $this->assertInvalidationStoreAction($data, 'array');
        $this->assertInvalidationUpdateAction($data, 'array');

        $data = [
            'genres_id' => ["100"],

        ];

        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');


    }

    public function testInvalidationOpenedField()
    {
        $data = [
            'opened' => 's',

        ];

        $this->assertInvalidationStoreAction($data, 'boolean');
        $this->assertInvalidationUpdateAction($data, 'boolean');

    }

    public function testInvalidationRatingField()
    {
        $data = [
            'rating' => 0,

        ];

        $this->assertInvalidationStoreAction($data, 'in');
        $this->assertInvalidationUpdateAction($data, 'in');

    }

    public function testSave(){
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();

        $genre->categories()->sync($category->id);

        $data = [
            [
                'send_data' => $this->sendData+ [
                    'categories_id' => [$category->id],
                    'genres_id' => [$genre->id],

                    ],
                'test_data' => $this->sendData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + [
                    'opened' => true,
                    'categories_id' => [$category->id],
                    'genres_id' => [$genre->id],
                    ],
                'test_data' => $this->sendData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + [
                    'rating' => Video::RATING_LIST[1],
                    'categories_id' => [$category->id],
                    'genres_id' => [$genre->id],
                    ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]]
            ]
        ];

        foreach($data as $key => $value) {
            $response = $this->assertStore(
                $value['send_data'], $value['test_data'] + ['deleted_at' => null]
            );

            $response->assertJsonStructure([
                'created_at',
                'updated_at',

            ]);

            $response = $this->assertUpdate(
                $value['send_data'], $value['test_data'] + ['deleted_at' => null]
            );

            $response->assertJsonStructure([
                'created_at',
                'updated_at',

            ]);

        }
    }





    public function testDestroy()
    {
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    public function  testInvalidationVideoField(){

        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            '2048',
            'mimetypes', ['values' => 'video/mp4']
        );
    }



    public function assertHasCategory($videoId, $categoryId){
        $this->assertDatabaseHas('category_video',[
           'video_id' => $videoId,
           'category_id' => $categoryId
        ]);

    }

    public function assertHasGenre($videoId, $genreId){
        $this->assertDatabaseHas('genre_video',[
            'video_id' => $videoId,
            'genre_id' => $genreId
        ]);

    }


    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}