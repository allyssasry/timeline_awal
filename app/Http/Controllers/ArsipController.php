<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ArsipController extends Controller
{
    public function index(Request $request)
    {
        // filter dari form
        $q     = (string) $request->query('q', '');
        $from  = $request->query('from'); // YYYY-MM-DD
        $to    = $request->query('to');   // YYYY-MM-DD
        $sort  = $request->query('sort', 'finished_desc');

        // === BASE QUERY (sesuai snippet yang diminta) ===
        $projects = Project::with([
                // eager load ringkas sesuai snippet:
                'progresses.updates',
                // batasi kolom nama untuk relasi user
                'digitalBanking:id,name',
                'developer:id,name',
            ])
            ->whereNotNull('completed_at')
            ->whereNotNull('meets_requirement');

        // === FILTER: keyword ke name/description ===
        if ($q !== '') {
            $projects->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }

        // === FILTER: rentang tanggal selesai (pakai kolom completed_at) ===
        if ($from) {
            $projects->whereDate('completed_at', '>=', $from);
        }
        if ($to) {
            $projects->whereDate('completed_at', '<=', $to);
        }

        // === SORTING ===
        // Default mengikuti snippet: latest('completed_at')
        if ($sort === 'finished_desc' || empty($sort)) {
            $projects->latest('completed_at');
        } else {
            switch ($sort) {
                case 'finished_asc':
                    $projects->orderBy('completed_at', 'asc');
                    break;
                case 'name_asc':
                    $projects->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $projects->orderBy('name', 'desc');
                    break;
                default:
                    $projects->latest('completed_at');
            }
        }

        // === PAGINATION ===
        $projects = $projects->paginate(12)->withQueryString();

        return view('semua.arsip', compact('projects'));
    }
}
