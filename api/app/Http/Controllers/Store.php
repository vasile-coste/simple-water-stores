<?php

namespace App\Http\Controllers;

use App\Models\Store as ModelsStore;
use App\Models\Water as ModelsWater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Store extends Controller
{
    public function getStore(int $id)
    {
        $store = ModelsStore::find($id);

        return response()->json([
            "success" => true,
            "data" => $store
        ]);
    }

    public function getAllStores()
    {
        $stores = ModelsStore::all();

        $store = app(ModelsStore::class)->getTable();
        $water = app(ModelsWater::class)->getTable();

        $stores = DB::table($store)
            ->select($store . '.*', DB::raw('COUNT(`' . $water . '`.`store_id`) as `water_brands`'))
            ->leftJoin($water, $water . '.store_id', '=', $store . '.id')
            ->groupBy($store . '.id')
            ->orderBy($store . '.id', 'ASC')
            ->get();

        return response()->json([
            "success" => true,
            "data" => $stores->all()
        ]);
    }

    public function addStore(Request $request)
    {
        $data = $request->toArray();

        $err = [];
        if (!isset($data['name']) || $data['name'] == "") {
            $err[] = "Please enter Store Name.";
        }
        if (!isset($data['address']) || $data['address'] == "") {
            $err[] = "Please enter Store Address.";
        }

        if (count($err) > 0) {
            return response()->json([
                "success" => false,
                "message" => implode(" ", $err)
            ]);
        }

        $save = new ModelsStore($data);
        $save->save();

        $store = ModelsStore::find($save->id);
        $store['water_brands'] = 0;

        return response()->json([
            "success" => true,
            "data" => $store,
            "message" => "Store added successfully."
        ]);
    }

    public function updateStore(Request $request)
    {

        $data = $request->toArray();

        $err = [];
        if (!isset($data['name']) || $data['name'] == "") {
            $err[] = "Please enter Store Name.";
        }
        if (!isset($data['address']) || $data['address'] == "") {
            $err[] = "Please enter Store Address.";
        }

        if (count($err) > 0) {
            return response()->json([
                "success" => false,
                "message" => implode(" ", $err)
            ]);
        }

        ModelsStore::where('id', $data['id'])
            ->update([
                'name' => $data['name'],
                'address' => $data['address']
            ]);

        return response()->json([
            "success" => true,
            "message" => "Store updated successfully."
        ]);
    }

    public function deleteStore(Request $request)
    {
        $data = $request->toArray();

        if (!isset($data['id']) || $data['id'] == "") {
            return response()->json([
                "success" => false,
                "message" => "Something is missing, please try again later."
            ]);
        }

        (ModelsStore::find($data['id']))->delete();
        
        (ModelsWater::where('store_id', $data['id']))->delete();

        return response()->json([
            "success" => true,
            "message" => "Store deleted successfully."
        ]);
    }
}
