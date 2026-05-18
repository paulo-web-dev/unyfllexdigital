<?php

/*
|--------------------------------------------------------------------------
| INSTRUÇÃO — substitua apenas os métodos cursoStore() e cursoUpdate()
| no seu AdminController.php
|--------------------------------------------------------------------------
*/

// ══════════════════════════════════════════════════════════════════════
// CURSOS — salvar novo (POST)
// ══════════════════════════════════════════════════════════════════════
public function cursoStore(Request $request)
{
    $request->validate([
        'title'      => ['required', 'string', 'max:255'],
        'subtitle'   => ['nullable', 'string', 'max:255'],
        'slug'       => ['required', 'string', 'max:255', 'unique:classes,slug'],
        'workload'   => ['nullable', 'string', 'max:100'],
        'valor'      => ['nullable', 'integer'],
        'status'     => ['required', 'in:able,disabled'],
        'live'       => ['nullable', 'boolean'],
        'start_date' => ['nullable', 'date'],
        'end_date'   => ['nullable', 'date'],
        'photo'      => ['nullable', 'string', 'max:255'],
        'info'       => ['nullable', 'string', 'max:255'],
        'polo'       => ['nullable', 'string', 'max:255'],
        'incompany'  => ['nullable', 'boolean'],
        'novidade'   => ['nullable', 'boolean'],
    ]);

    $classe = Classes::create([
        'title'      => $request->title,
        'subtitle'   => $request->subtitle,
        'slug'       => Str::slug($request->slug),
        'workload'   => $request->workload,
        'valor'      => $request->valor ?? 0,
        'status'     => $request->status,
        'live'       => $request->boolean('live') ? 1 : 0,
        'start_date' => $request->start_date,
        'end_date'   => $request->end_date,
        'photo'      => $request->photo,
        'info'       => $request->info,
        'polo'       => $request->polo,
        'incompany'  => $request->boolean('incompany') ? 1 : 0,
        'novidade'   => $request->boolean('novidade') ? 1 : 0,
        // Flags fixas para minissérie
        'express'    => 1,
        'cc'         => 0,
        'mc'         => 0,
        'cv'         => 0,
        'seminario'  => 0,
        'confirmed'  => 0,
    ]);

    return redirect()
        ->route('admin.cursos.show', $classe->id)
        ->with('success', 'Minissérie criada com sucesso!');
}

// ══════════════════════════════════════════════════════════════════════
// CURSOS — salvar edição (PUT)
// ══════════════════════════════════════════════════════════════════════
public function cursoUpdate(Request $request, int $id)
{
    $request->validate([
        'title'      => ['required', 'string', 'max:255'],
        'subtitle'   => ['nullable', 'string', 'max:255'],
        'slug'       => ['required', 'string', 'max:255'],
        'workload'   => ['nullable', 'string', 'max:100'],
        'valor'      => ['nullable', 'integer'],
        'status'     => ['required', 'in:able,disabled'],
        'live'       => ['nullable', 'boolean'],
        'start_date' => ['nullable', 'date'],
        'end_date'   => ['nullable', 'date'],
        'photo'      => ['nullable', 'string', 'max:255'],
        'info'       => ['nullable', 'string', 'max:255'],
        'polo'       => ['nullable', 'string', 'max:255'],
        'incompany'  => ['nullable', 'boolean'],
        'novidade'   => ['nullable', 'boolean'],
    ]);

    $classe = Classes::findOrFail($id);
    $classe->title      = $request->title;
    $classe->subtitle   = $request->subtitle;
    $classe->slug       = Str::slug($request->slug);
    $classe->workload   = $request->workload;
    $classe->valor      = $request->valor ?? $classe->valor;
    $classe->status     = $request->status;
    $classe->live       = $request->boolean('live') ? 1 : 0;
    $classe->start_date = $request->start_date;
    $classe->end_date   = $request->end_date;
    $classe->photo      = $request->photo;
    $classe->info       = $request->info;
    $classe->polo       = $request->polo;
    $classe->incompany  = $request->boolean('incompany') ? 1 : 0;
    $classe->novidade   = $request->boolean('novidade') ? 1 : 0;
    $classe->express    = 1; // garante que nunca perde o flag
    $classe->save();

    return redirect()
        ->route('admin.cursos.show', $id)
        ->with('success', 'Minissérie atualizada com sucesso!');
}
