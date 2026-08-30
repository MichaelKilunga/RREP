<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'file_name' => 'required|string',
            'document_type' => 'required|string', // Title Deed, Cadastral Survey Plan, Sale Agreement, KYC Document, Tax Clearance
            'file' => 'nullable|file|max:20480', // up to 20MB
        ]);

        $filePath = 'documents/sample_'.time().'.pdf';
        $fileSize = 1024 * 512; // 512 KB default
        $mimeType = 'application/pdf';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('documents', 'public');
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
        }

        MediaFile::create([
            'user_id' => Auth::id() ?? 1,
            'file_name' => $request->file_name,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'category' => $request->document_type,
            'disk' => 'public',
        ]);

        return back()->with('success', 'Document securely archived in EDMS Document Vault.');
    }
}
