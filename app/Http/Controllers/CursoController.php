<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes; 
use App\Models\Panel; 
use App\Models\Degree; 
use App\Models\MaterialPanels; 
use App\Models\Token; 
use Auth;

class CursoController extends Controller
{
    public function index()
    {

        $classes = Classes::where('express', '1')->where('status', 'able')->where('id', '>', 1900)->get();
        return view('pages.cursos',[
            'classes' => $classes
        ]);
    }

    public function show($slug)
    {
        if($slug == 'Transicao-e-Final-de-Mandato-Outubro-2024-online'){
            $slug = 'Transicao-e-Final-de-Mandato-Outubro-2024';
        }
 
        $hoje = date('Y-m-d');
         
        $classes=Classes::where('slug', $slug)->with('panels')->first();  
       
 $id = $classes->id;
        $cursoid=$classes->course_id;       
        $classesId=Classes::where('course_id', $cursoid)->with('panels')->get();   
         
        
        $panel = Panel::where('classes_id', $id)
        ->with('material')
        ->orderBy('start_time', 'ASC')
        ->orderByRaw("CAST(horario AS TIME)")
        ->get();

    $titles = $panel->pluck('title')->unique();
    $startTimes = $panel->pluck('start_time')->unique();
    
    $matchingPanels = Panel::whereIn('title', $titles)
    ->whereIn('start_time', $startTimes)
    ->with('material')
    ->get()
    ->keyBy('id'); // Garante que usamos o ID como chave principal


$groupedByTitle = $matchingPanels->groupBy('title');

$mergedPanels = collect();

foreach ($groupedByTitle as $panelsWithSameTitle) {
 
    $panelWithMaxKey = $panelsWithSameTitle->sortByDesc('id')->first();

 
    foreach ($panelsWithSameTitle as $panels) {
        if ($panels->id !== $panelWithMaxKey->id) {
          
            if ($panels->relationLoaded('material') && $panelWithMaxKey->relationLoaded('material')) {
                $mergedMaterials = $panelWithMaxKey->material->merge($panels->material);
                $panelWithMaxKey->setRelation('material', $mergedMaterials->unique('id'));
            }
        }
    }

    $mergedPanels->put($panelWithMaxKey->id, $panelWithMaxKey);
}
 

    
     

        return view('pages.curso-show', [
           
            'classes' => $classes,
            'panel' => $panel ,
            'hoje' => $hoje,
        ]);
    }


    public function showPos($slug)
    {
        if($slug == 'Transicao-e-Final-de-Mandato-Outubro-2024-online'){
            $slug = 'Transicao-e-Final-de-Mandato-Outubro-2024';
        }
 
        $hoje = date('Y-m-d');
         
        $classes=Classes::where('slug', $slug)->with('panels')->first();  
       
 $id = $classes->id;
        $cursoid=$classes->course_id;       
        $classesId=Classes::where('course_id', $cursoid)->with('panels')->get();   
         
        
        $panel = Panel::where('classes_id', $id)
        ->with('material')
        ->orderBy('start_time', 'ASC')
        ->orderByRaw("CAST(horario AS TIME)")
        ->get();

    $titles = $panel->pluck('title')->unique();
    $startTimes = $panel->pluck('start_time')->unique();
    
    $matchingPanels = Panel::whereIn('title', $titles)
    ->whereIn('start_time', $startTimes)
    ->with('material')
    ->get()
    ->keyBy('id'); // Garante que usamos o ID como chave principal


$groupedByTitle = $matchingPanels->groupBy('title');

$mergedPanels = collect();

foreach ($groupedByTitle as $panelsWithSameTitle) {
 
    $panelWithMaxKey = $panelsWithSameTitle->sortByDesc('id')->first();

 
    foreach ($panelsWithSameTitle as $panels) {
        if ($panels->id !== $panelWithMaxKey->id) {
          
            if ($panels->relationLoaded('material') && $panelWithMaxKey->relationLoaded('material')) {
                $mergedMaterials = $panelWithMaxKey->material->merge($panels->material);
                $panelWithMaxKey->setRelation('material', $mergedMaterials->unique('id'));
            }
        }
    }

    $mergedPanels->put($panelWithMaxKey->id, $panelWithMaxKey);
}
 

    
     

        return view('pages.curso-show-pos', [
           
            'classes' => $classes,
            'panel' => $panel ,
            'hoje' => $hoje,
        ]);
    }
}
