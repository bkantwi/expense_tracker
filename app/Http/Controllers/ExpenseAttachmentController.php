<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseAttachment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ExpenseAttachmentController extends Controller
{
    // POST /expenses/{expense}/attachments
    public function store(Request $request, Expense $expense)
    {
        // authorize by expense ownership (assumes ExpensePolicy@update exists)
        $this->authorize('update', $expense);

        $request->validate([
            'files.*' => [
                'required','file','max:5120', // 5MB per file
                'mimes:jpg,jpeg,png,webp,pdf', // adjust as needed
            ],
        ]);

        $saved = 0;
        foreach ((array) $request->file('files', []) as $file) {
            $path = $file->store('receipts', 'public');
            ExpenseAttachment::create([
                'user_id'       => Auth::id(),
                'expense_id'    => $expense->id,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getClientMimeType(),
                'size'          => $file->getSize(),
            ]);
            $saved++;
        }

        return back()->with('success', $saved.' file(s) uploaded.');
    }

    // GET /attachments/{attachment}/download
    public function download(ExpenseAttachment $attachment)
    {
        $this->authorizeThroughExpense($attachment);

        return Response::download(
            Storage::disk('public')->path($attachment->path),
            $attachment->original_name
        );
    }

    // DELETE /attachments/{attachment}
    public function destroy(ExpenseAttachment $attachment)
    {
        $this->authorizeThroughExpense($attachment);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    /**
     * @throws AuthorizationException
     */
    private function authorizeThroughExpense(ExpenseAttachment $attachment): void
    {
        // Gate via the related expense
        $this->authorize('view', $attachment->expense);
        // or 'update' if you only want owners to download
    }
}
