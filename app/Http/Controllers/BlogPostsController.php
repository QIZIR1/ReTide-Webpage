<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogPostsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // ?saved=1 -> hanya tampilkan artikel yang di-bookmark user yang login
        $savedOnly = $request->boolean('saved');

        // ?tag=... -> hanya tampilkan artikel dengan tag tersebut
        $tagInput = trim((string) $request->query('tag', ''));
        $activeTag = $tagInput !== '' ? (BlogPost::normalizeTag($tagInput) ?: null) : null;

        $blog = BlogPost::query()
            ->when($savedOnly, function ($query) {
                $query->whereHas('bookmarkedBy', function ($q) {
                    $q->where('users.id', auth()->id());
                });
            })
            ->when($tagInput !== '', fn ($query) => $query->withTag($tagInput))
            ->latest()
            ->get();

        return view('blog.index', [
            'blog' => $blog,
            'savedOnly' => $savedOnly,
            'activeTag' => $activeTag,
            'popularTags' => BlogPost::popularTags(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('blog.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:5|max:100',
            'content' => 'required|min:10|max:5000',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'tags' => 'nullable|string|max:255',
        ]);

        $imagePath = null;
        if ($request->hasFile('image_path')) {
            try {
                $imagePath = cloudinary()->uploadApi()->upload($request->file('image_path')->getRealPath(), ['folder' => 'blog_posts'])['secure_url'];
            } catch (\Throwable $e) {
                $path = $request->file('image_path')->store('blog_posts', 'public');
                $imagePath = asset('storage/'.$path);
            }
        }

        BlogPost::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'image_path' => $imagePath,
            'tags' => BlogPost::parseTags($request->input('tags')),
        ]);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $blogPost = BlogPost::findOrFail($id);
        $isBookmarked = auth()->user()->hasBookmarked($blogPost);

        return view('blog.show', compact('blogPost', 'isBookmarked'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $blog = BlogPost::findOrFail($id);

        return view('blog.edit', compact('blog'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $blog = BlogPost::findOrFail($id);

        $request->validate([
            'title' => 'required|min:5|max:100',
            'content' => 'required|min:10|max:5000',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'tags' => 'nullable|string|max:255',
        ]);

        $data = [
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'tags' => BlogPost::parseTags($request->input('tags')),
        ];

        if ($request->hasFile('image_path')) {
            try {
                $data['image_path'] = cloudinary()->uploadApi()->upload($request->file('image_path')->getRealPath(), ['folder' => 'blog_posts'])['secure_url'];
            } catch (\Throwable $e) {
                $path = $request->file('image_path')->store('blog_posts', 'public');
                $data['image_path'] = asset('storage/'.$path);
            }
        }

        $blog->update($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blog = BlogPost::findOrFail($id);
        $blog->delete();

        return redirect()->route('admin.blogs.index')->with('success', 'Blog berhasil dihapus!');
    }
}
