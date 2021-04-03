<?php

namespace App\Http\Controllers;

use App\Models\Store as ModelsStore;
use App\Models\Water as ModelsWater;
use Illuminate\Http\Request;

class Water extends Controller
{

    public function randomUpdateWaterStock(int $storeId, int $waterId)
    {
        $store = ModelsStore::find($storeId);
        if (!$store) {
            return response()->json([
                "success" => false,
                "message" => "The store was not found."
            ]);
        }

        $water = ModelsWater::find($waterId);
        if (!$water) {
            return response()->json([
                "success" => false,
                "message" => "The water was not found."
            ]);
        }

        $hour = number_format(date("H"));
        $newStock = $water['stock'] - 1;
        if ($hour > 1  && $hour <= 5) {
            $newStock = $water['stock'] - 5;
        }
        if ($hour > 6  && $hour <= 10) {
            $newStock = $water['stock'] - 10;
        }
        if ($hour > 11  && $hour <= 15) {
            $newStock = $water['stock'] - 15;
        }
        if ($hour > 15  && $hour <= 20) {
            $newStock = $water['stock'] - 20;
        }

        $newStock = $newStock < 0 ? 0 : $newStock;

        ModelsWater::find($waterId)->update(['stock' => $newStock]);

        return response()->json([
            "success" => true,
            "message" => sprintf(
                'Stock for %s water was updated form %d to %d in %s store.',
                $water['name'],
                $water['stock'],
                $newStock,
                $store['name']
            )
        ]);
    }

    public function getAllWater(int $storeId)
    {
        $waters = ModelsWater::where('store_id', $storeId)->get();

        return response()->json([
            "success" => true,
            "data" => $waters->all()
        ]);
    }

    public function addWater(Request $request)
    {
        $data = $request->toArray();

        if (!isset($data['store_id']) || $data['store_id'] == "") {
            return response()->json([
                "success" => false,
                "message" => "Something is missing, please try again later."
            ]);
        }

        $err = [];
        if (!isset($data['name']) || $data['name'] == "") {
            $err[] = "Please enter Water Name.";
        }
        if (!isset($data['stock']) || $data['stock'] == "") {
            $err[] = "Please enter Water Stock.";
        }

        if (count($err) > 0) {
            return response()->json([
                "success" => false,
                "message" => implode(" ", $err)
            ]);
        }


        $save = new ModelsWater($data);
        $save->save();

        $water = ModelsWater::find($save->id);

        return response()->json([
            "success" => true,
            "data" => $water,
            "message" => "Water added successfully."
        ]);
    }

    public function updateWater(Request $request)
    {
        $data = $request->toArray();

        $err = [];
        if (!isset($data['name']) || $data['name'] == "") {
            $err[] = "Please enter Water Name.";
        }
        if (!isset($data['stock']) || $data['stock'] == "") {
            $err[] = "Please enter Water Stock.";
        }

        if (count($err) > 0) {
            return response()->json([
                "success" => false,
                "message" => implode(" ", $err)
            ]);
        }

        ModelsWater::where('id', $data['id'])
            ->update([
                'name' => $data['name'],
                'stock' => $data['stock']
            ]);

        return response()->json([
            "success" => true,
            "message" => "Water updated successfully."
        ]);
    }

    public function deleteWater(Request $request)
    {
        $data = $request->toArray();

        if (!isset($data['id']) || $data['id'] == "") {
            return response()->json([
                "success" => false,
                "message" => "Something is missing, please try again later."
            ]);
        }

        (ModelsWater::find($data['id']))->delete();

        return response()->json([
            "success" => true,
            "message" => "Water deleted successfully."
        ]);
    }
}
