<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Championship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChampionshipController extends Controller
{
    public function index(Request $request)
    {
        $query = Championship::with(['category', 'user'])->withCount('races', 'inscriptions');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $championships = $query->latest()->paginate(15)->withQueryString();

        return view('admin.championships.index', compact('championships'));
    }

    public function create()
    {
        return view('admin.championships.form', [
            'championship' => new Championship(['status' => 'draft', 'season_year' => now()->year]),
            'categories'   => Category::orderBy('name')->get(),
            'organizers'   => User::whereIn('role', ['organizer', 'admin'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['status'] = $request->input('status', 'draft');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('championships', 'public');
        }

        $championship = Championship::create($data);

        return redirect()->route('admin.championships.show', $championship)
            ->with('success', 'Campeonato creado.');
    }

    public function show(Championship $championship)
    {
        $championship->load([
            'category',
            'user',
            'races' => fn ($q) => $q->with('circuit')->withCount('inscriptions')->orderBy('scheduled_at'),
        ]);
        $championship->loadCount('inscriptions');

        $standings = DB::table('results')
            ->join('races', 'results.race_id', '=', 'races.id')
            ->join('users', 'results.user_id', '=', 'users.id')
            ->where('races.championship_id', $championship->id)
            ->select('users.id', 'users.name', DB::raw('SUM(results.points) as total_points'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_points')
            ->get();

        return view('admin.championships.show', compact('championship', 'standings'));
    }

    public function edit(Championship $championship)
    {
        return view('admin.championships.form', [
            'championship' => $championship,
            'categories'   => Category::orderBy('name')->get(),
            'organizers'   => User::whereIn('role', ['organizer', 'admin'])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Championship $championship)
    {
        $data = $this->validated($request, $championship);

        if ($request->hasFile('image')) {
            if ($championship->image) {
                Storage::disk('public')->delete($championship->image);
            }
            $data['image'] = $request->file('image')->store('championships', 'public');
        }

        $championship->update($data);

        return redirect()->route('admin.championships.show', $championship)
            ->with('success', 'Campeonato actualizado.');
    }

    public function destroy(Championship $championship)
    {
        if ($championship->image) {
            Storage::disk('public')->delete($championship->image);
        }
        $championship->delete();

        return redirect()->route('admin.championships.index')->with('success', 'Campeonato eliminado.');
    }

    public function updateStatus(Request $request, Championship $championship)
    {
        $request->validate([
            'status' => 'required|in:draft,published,in_progress,finished,cancelled',
        ]);

        $championship->update(['status' => $request->status]);

        return back()->with('success', 'Estado actualizado a '.$request->status.'.');
    }

    private function validated(Request $request, ?Championship $championship = null): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'user_id'     => ['required', 'exists:users,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'kart_modality' => ['nullable', 'in:rental,own'],
            'engine_class' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'season_year' => ['required', 'integer', 'min:2020', 'max:2035'],
            'start_date'  => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'          => ['sometimes', 'in:draft,published,in_progress,finished,cancelled'],
            'image'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'venue_country'   => ['nullable', 'string', 'max:255'],
            'venue_province'  => ['nullable', 'string', 'max:255'],
            'venue_city'      => ['nullable', 'string', 'max:255'],
            'venue_latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'venue_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
    }
}
