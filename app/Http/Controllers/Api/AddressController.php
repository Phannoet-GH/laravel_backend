<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Address;
use App\Security\Sanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $userId = Sanitizer::cleanString($request->query('user_id', 'guest'), 60);

        $addresses = Address::where('user_id', $userId)
            ->orderBy('is_default', 'desc')
            ->latest()
            ->get();

        return $this->success($addresses);
    }

    public function store(Request $request): JsonResponse
    {
        $userId = Sanitizer::cleanString($request->input('user_id', 'guest'), 60);
        $id = $request->filled('id')
            ? Sanitizer::cleanString($request->input('id'), 60)
            : ('addr_' . time() . '_' . bin2hex(random_bytes(3)));

        $recipientName = Sanitizer::cleanString($request->input('recipient_name', 'Emma Wills'), 150);
        $street = Sanitizer::cleanString($request->input('street', ''), 255);
        $city = Sanitizer::cleanString($request->input('city', ''), 100);
        $state = Sanitizer::cleanString($request->input('state', ''), 50);
        $zipCode = Sanitizer::cleanString($request->input('zip_code', ''), 30);
        $country = Sanitizer::cleanString($request->input('country', 'United States'), 100);
        $phone = Sanitizer::cleanString($request->input('phone', '+1 (555) 000-0000'), 50);
        $isDefault = filter_var($request->input('is_default', false), FILTER_VALIDATE_BOOLEAN);

        if ($isDefault && $userId !== 'guest') {
            Address::where('user_id', $userId)->update(['is_default' => false]);
        }

        $address = Address::create([
            'id' => $id,
            'user_id' => $userId,
            'recipient_name' => $recipientName,
            'street' => $street,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zipCode,
            'country' => $country,
            'phone' => $phone,
            'is_default' => $isDefault,
        ]);

        return $this->success($address, [], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $id = Sanitizer::cleanString($request->input('id') ?: $request->query('id'), 60);
        $userId = $request->has('user_id') ? Sanitizer::cleanString($request->input('user_id') ?: $request->query('user_id'), 60) : null;

        if (empty($id)) {
            return $this->error('Address ID is required.', 400);
        }

        $query = Address::where('id', $id);
        if ($userId) {
            $query->where('user_id', $userId);
        }
        $query->delete();

        return $this->success(['success' => true, 'message' => 'Address deleted.']);
    }

    public function setDefault(Request $request): JsonResponse
    {
        $id = Sanitizer::cleanString($request->input('id', ''), 60);
        $userId = Sanitizer::cleanString($request->input('user_id', 'guest'), 60);

        if (empty($id)) {
            return $this->error('Address ID is required.', 400);
        }

        if ($userId !== 'guest') {
            Address::where('user_id', $userId)->update(['is_default' => false]);
        }

        Address::where('id', $id)->update(['is_default' => true]);

        return $this->success(['success' => true, 'message' => 'Default address updated.']);
    }
}
