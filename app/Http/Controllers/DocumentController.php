<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = MediaFile::with('user')->latest()->paginate(20);
        $properties = Property::all();

        return view('documents.index', compact('documents', 'properties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string|max:255',
            'document_type' => 'required|string|max:120',
            'file' => 'required|file|max:25600|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,csv,txt,jpg,jpeg,png,webp,gif,zip,kml,kmz,dwg,dxf',
        ]);

        $file = $request->file('file');

        MediaFile::create([
            'user_id' => Auth::id() ?? 1,
            'file_name' => $request->file_name,
            'file_path' => $file->store('documents', 'public'),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'category' => $request->document_type,
            'disk' => 'public',
        ]);

        return back()->with('success', __('admin.document_archived'));
    }

    public function destroy(MediaFile $document)
    {
        if ($document->file_path && Storage::disk($document->disk ?: 'public')->exists($document->file_path)) {
            Storage::disk($document->disk ?: 'public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', __('admin.document_deleted'));
    }
}
