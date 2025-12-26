<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

/**
 * @group Contacts
 *
 * APIs for managing contacts
 */
class ContactController extends Controller
{
    /**
     * List all contacts
     *
     * Get a list of all contacts for the authenticated user.
     *
     * @authenticated
     * @queryParam search string Search by name or phone. Example: John
     * @queryParam group_id integer Filter by group ID. Example: 1
     * @queryParam per_page integer Number of items per page (default: 50). Example: 20
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "John Doe",
     *       "phone_number": "+628123456789",
     *       "email": "john@example.com",
     *       "group": {"id": 1, "name": "VIP"}
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "total": 100
     *   }
     * }
     */
    public function index(Request $request)
    {
        $query = $request->user()->contacts()->with('group');

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->group_id) {
            $query->where('contact_group_id', $request->group_id);
        }

        $contacts = $query->paginate($request->per_page ?? 50);

        return response()->json([
            'success' => true,
            'data' => $contacts->items(),
            'meta' => [
                'current_page' => $contacts->currentPage(),
                'total' => $contacts->total(),
                'per_page' => $contacts->perPage(),
                'last_page' => $contacts->lastPage(),
            ],
        ]);
    }

    /**
     * Create a new contact
     *
     * Add a new contact to your list.
     *
     * @authenticated
     * @bodyParam name string required The contact's name. Example: John Doe
     * @bodyParam phone_number string required The phone number (international format). Example: +628123456789
     * @bodyParam email string Email address (optional). Example: john@example.com
     * @bodyParam contact_group_id integer Group ID (optional). Example: 1
     * @response 201 {
     *   "success": true,
     *   "message": "Contact created successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "phone_number": "+628123456789"
     *   }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:100',
            'phone_number' => 'required|string|min:10|max:20',
            'email' => 'nullable|email',
            'contact_group_id' => 'nullable|exists:contact_groups,id',
        ]);

        $contact = $request->user()->contacts()->create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'contact_group_id' => $request->contact_group_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contact created successfully',
            'data' => $contact,
        ], 201);
    }

    /**
     * Get contact details
     *
     * @authenticated
     * @urlParam id integer required The contact ID. Example: 1
     */
    public function show(Request $request, $id)
    {
        $contact = $request->user()->contacts()->with('group')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $contact,
        ]);
    }

    /**
     * Update a contact
     *
     * @authenticated
     * @urlParam id integer required The contact ID. Example: 1
     * @bodyParam name string The contact's name. Example: John Doe
     * @bodyParam phone_number string The phone number. Example: +628123456789
     * @bodyParam email string Email address. Example: john@example.com
     */
    public function update(Request $request, $id)
    {
        $contact = $request->user()->contacts()->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|min:2|max:100',
            'phone_number' => 'sometimes|string|min:10|max:20',
            'email' => 'nullable|email',
            'contact_group_id' => 'nullable|exists:contact_groups,id',
        ]);

        $contact->update($request->only(['name', 'phone_number', 'email', 'contact_group_id']));

        return response()->json([
            'success' => true,
            'message' => 'Contact updated successfully',
            'data' => $contact,
        ]);
    }

    /**
     * Delete a contact
     *
     * @authenticated
     * @urlParam id integer required The contact ID. Example: 1
     */
    public function destroy(Request $request, $id)
    {
        $contact = $request->user()->contacts()->findOrFail($id);
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully',
        ]);
    }
}
