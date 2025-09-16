@extends('layouts.app')

@section('title','Edit Project')

@section('content')
  <h1 class="text-lg font-semibold mb-4">Edit Project</h1>

  <form method="POST" action="{{ route('projects.update', $project->id) }}" class="space-y-4 bg-white p-5 rounded-xl border">
    @csrf
    @method('PUT')

    <div>
      <label class="block text-sm font-medium">Nama Project</label>
      <input name="name" value="{{ old('name', $project->name) }}" required class="mt-1 w-full border rounded px-3 py-2">
      @error('name')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium">Penanggung Jawab (DIG)</label>
        <select name="digital_banking_id" class="mt-1 w-full border rounded px-3 py-2" required>
          @foreach($digitalUsers as $u)
            <option value="{{ $u->id }}" @selected(old('digital_banking_id',$project->digital_banking_id)==$u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
        @error('digital_banking_id')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Penanggung Jawab (IT)</label>
        <select name="developer_id" class="mt-1 w-full border rounded px-3 py-2" required>
          @foreach($itUsers as $u)
            <option value="{{ $u->id }}" @selected(old('developer_id',$project->developer_id)==$u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
        @error('developer_id')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium">Deskripsi</label>
      <textarea name="description" rows="4" class="mt-1 w-full border rounded px-3 py-2">{{ old('description',$project->description) }}</textarea>
      @error('description')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
    </div>

    <div class="flex gap-2 justify-end">
      <a href="{{ route('dig.dashboard') }}" class="px-4 py-2 border rounded">Batal</a>
      <button class="px-4 py-2 bg-[#7A1C1C] text-white rounded">Simpan</button>
    </div>
  </form>
@endsection
