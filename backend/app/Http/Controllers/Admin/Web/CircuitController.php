<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Circuit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CircuitController extends Controller
{
    public function index(Request $request)
    {
        $query = Circuit::with('user')->withCount('races');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('province')) {
            $query->where('province', $request->province);
        }

        $circuits = $query->latest()->paginate(15)->withQueryString();

        return view('admin.circuits.index', compact('circuits'));
    }

    public function create()
    {
        return view('admin.circuits.form', [
            'circuit'    => new Circuit(),
            'organizers' => User::whereIn('role', ['organizer', 'admin'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('circuits', 'public');
        }

        Circuit::create(array_merge($data, ['status' => $data['status'] ?? 'approved']));

        return redirect()->route('admin.circuits.index')->with('success', 'Circuito creado.');
    }

    public function edit(Circuit $circuit)
    {
        return view('admin.circuits.form', [
            'circuit'    => $circuit,
            'organizers' => User::whereIn('role', ['organizer', 'admin'])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Circuit $circuit)
    {
        $data = $this->validated($request, $circuit);

        if ($request->hasFile('image')) {
            if ($circuit->image) {
                Storage::disk('public')->delete($circuit->image);
            }
            $data['image'] = $request->file('image')->store('circuits', 'public');
        }

        $circuit->update($data);

        return redirect()->route('admin.circuits.index')->with('success', 'Circuito actualizado.');
    }

    public function updateStatus(Request $request, Circuit $circuit)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $previousStatus = $circuit->status;
        $circuit->update(['status' => $request->status]);
        $fresh = $circuit->fresh()->load('user');

        if (
            in_array($request->status, ['approved', 'rejected'], true)
            && $request->status !== $previousStatus
            && $fresh->user?->email
        ) {
            \App\Support\MailHelper::sendSafely(
                $fresh->user->email,
                new \App\Mail\CircuitStatusMail($fresh),
                ['circuit_id' => $fresh->id, 'type' => 'circuit_status'],
            );
        }

        return back()->with('success', 'Estado del circuito actualizado.');
    }

    public function destroy(Circuit $circuit)
    {
        if ($circuit->image) {
            Storage::disk('public')->delete($circuit->image);
        }
        $circuit->delete();

        return redirect()->route('admin.circuits.index')->with('success', 'Circuito eliminado.');
    }

    private function validated(Request $request, ?Circuit $circuit = null): array
    {
        return $request->validate([
            'user_id'       => ['required', 'exists:users,id'],
            'name'          => ['required', 'string', 'max:255'],
            'location'      => ['required', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:255'],
            'province'      => ['nullable', 'string', 'max:255'],
            'country'       => ['nullable', 'string', 'max:255'],
            'status'        => ['sometimes', 'in:pending,approved,rejected'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
            'length_meters' => ['nullable', 'integer', 'min:0'],
            'description'   => ['nullable', 'string'],
            'image'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);
    }
}
