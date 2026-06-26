<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Legacy\AspiranteLegacy;
use Illuminate\Http\Request;

class AspiranteController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Iniciamos la consulta a través del modelo que conecta a la base SQLite unificada
            $query = AspiranteLegacy::query();

            // Filtro por Ciclo / Calendario (Columna CALENDARIO)
            if ($request->filled('calendario') && !$request->boolean('global')) {
                $query->where('CALENDARIO', '=', trim($request->input('calendario')));
            }

            // Búsqueda inteligente integrada (Código o Nombre Completo)
            if ($request->filled('busqueda')) {
                $busqueda = trim($request->input('busqueda'));
                $query->where(function($q) use ($busqueda) {
                    $q->where('CODIGO', 'LIKE', "%{$busqueda}%")
                      ->orWhere('NOMBRE', 'LIKE', "%{$busqueda}%")
                      ->orWhere('APE_PAT', 'LIKE', "%{$busqueda}%")
                      ->orWhere('APE_MAT', 'LIKE', "%{$busqueda}%");
                });
            }

            // Ordenamiento dinámico
            $sortBy = $request->input('sort_by', 'CALENDARIO');
            $sortDir = $request->input('sort_dir', 'asc');
            $query->orderBy($sortBy, $sortDir);

            // Paginación nativa
            $perPage = intval($request->input('per_page', 50));
            $paginator = $query->paginate($perPage);

            // Transformación y normalización exacta de los datos para el Frontend de Vue
            $paginator->getCollection()->transform(function($item) {
                return [
                    'ID'             => $item->ID,
                    'CODIGO'         => $item->CODIGO,
                    'CALENDARIO'     => $item->CALENDARIO,
                    'CEDU_CARRERA'   => $item->CEDU_CARRERA,
                    'CEDU_SEDE'      => $item->CEDU_SEDE,
                    'CEDU_GRADO'     => $item->CEDU_GRADO,
                    'CEDU_PROMEDIO'  => $item->CEDU_PROMEDIO,
                    'CAPTURO'        => $item->CAPTURO,
                    'resultadoExam'  => $item->COLE_RESULTADO ?? $item->resultadoExam ?? '—',
                    'nombreCompleto' => trim(($item->APE_PAT ?? '') . ' ' . ($item->APE_MAT ?? '') . ' ' . ($item->NOMBRE ?? ''))
                ];
            });

            return response()->json($paginator, 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar la consulta en la base SQLite.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $item = AspiranteLegacy::find($id);

            if (!$item) {
                return response()->json(['message' => 'Registro no encontrado.'], 404);
            }

            // Retornamos el registro respetando el formato original esperado por el modal de Vue
            return response()->json([
                'ID'             => $item->ID,
                'CODIGO'         => $item->CODIGO,
                'CALENDARIO'     => $item->CALENDARIO,
                'APE_PAT'        => $item->APE_PAT,
                'APE_MAT'        => $item->APE_MAT,
                'NOMBRE'         => $item->NOMBRE,
                'FEC_NAC'        => $item->FEC_NAC,
                'DOMICILIO'      => $item->DOMICILIO,
                'COLONIA'        => $item->COLONIA,
                'CP'             => $item->CP,
                'TELEFONO'       => $item->TELEFONO,
                'ESTA_VIV'       => $item->ESTA_VIV,
                'nombreCompleto' => trim(($item->APE_PAT ?? '') . ' ' . ($item->APE_MAT ?? '') . ' ' . ($item->NOMBRE ?? ''))
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error en el servidor.', 'error' => $e->getMessage()], 500);
        }
    }

    public function examen($id)
    {
        try {
            $item = AspiranteLegacy::find($id);

            if (!$item) {
                return response()->json(['message' => 'Registro de examen no encontrado.'], 404);
            }

            return response()->json([
                'COLE_FECHA_EX'   => $item->COLE_FECHA_EX,
                'COLE_HABILIDAD'  => $item->COLE_HABILIDAD,
                'COLE_RESULTADO'  => $item->COLE_RESULTADO,
                'COLE_ESPANIOL'   => $item->COLE_ESPANIOL,
                'COLE_MATEMAT'    => $item->COLE_MATEMATAT, // Mapeado a la columna con triple T de tu base de datos
                'COLE_INGLES'     => $item->COLE_INGLES,
                'COLE_GRAMATICA'  => $item->COLE_GRAMATICA,
                'COLE_LITERATURA' => $item->COLE_LITERATURA,
                'COLE_ALGEBRA_B'  => $item->COLE_ALGEBRA_B,
                'COLE_GEOMETRIA'  => $item->COLE_GEOMETRIA,
                'COLE_LECTUR'     => $item->COLE_LECTUR,
                'COLE_TIPO'       => $item->COLE_TIPO,
                'resultadoExam'   => $item->COLE_RESULTADO ?? '—'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al leer el examen.', 'error' => $e->getMessage()], 500);
        }
    }
}