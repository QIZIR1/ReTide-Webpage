<?php

namespace Tests\Unit;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_post_fillable_attributes(): void
    {
        $post = new BlogPost;

        $this->assertEquals(['title', 'content', 'image_path', 'tags'], $post->getFillable());
    }

    public function test_blog_post_table_and_primary_key(): void
    {
        $post = new BlogPost;

        $this->assertEquals('blog_posts', $post->getTable());
        $this->assertEquals('id', $post->getKeyName());
    }

    public function test_can_create_blog_post(): void
    {
        $postData = [
            'title' => 'Sample Blog Title',
            'content' => 'This is the sample blog content. It must have at least ten characters.',
            'image_path' => 'https://res.cloudinary.com/dummy.jpg',
        ];

        $post = BlogPost::create($postData);

        $this->assertInstanceOf(BlogPost::class, $post);
        $this->assertDatabaseHas('blog_posts', $postData);
        $this->assertEquals('Sample Blog Title', $post->title);
        $this->assertEquals('This is the sample blog content. It must have at least ten characters.', $post->content);
        $this->assertEquals('https://res.cloudinary.com/dummy.jpg', $post->image_path);
    }
}
