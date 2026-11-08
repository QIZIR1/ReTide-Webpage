<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;

class BlogBookmarkController extends Controller
{
    /**
     * Menyimpan atau menghapus artikel dari bookmark user yang sedang login.
     *
     * Jika artikel belum disimpan, artikel ditambahkan ke bookmark.
     * Jika sudah disimpan, artikel dihapus dari bookmark.
     */
    public function toggle(BlogPost $blogPost): RedirectResponse
    {
        $result = auth()->user()->bookmarkedPosts()->toggle($blogPost->id);

        $message = count($result['attached']) > 0
            ? 'Artikel disimpan ke bookmark.'
            : 'Artikel dihapus dari bookmark.';

        return back()->with('success', $message);
    }
}
