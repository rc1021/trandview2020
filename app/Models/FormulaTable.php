<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DB;

class FormulaTable extends Model
{
    use HasFactory;

    private $m_spreadsheet;

    /**
     * 取得 excel 的 Spreadsheet 物件
     *
     * param $version int 版本號
     *
     * @return FormulaTable
     */
    public function scopeVersion($query, $version = 0)
    {
        if($version > 0)
            return $query->where('id', $version);
        return $query->orderBy('id', 'desc');
    }

    /**
     * 取得指定交易對公式表最新的資料
     *
     * @return FormulaTable
     */
    public function scopePair($query, $pair)
    {
        return $query->where('pair', $pair);
    }

    /**
     * 取得所有交易對公式表最新的資料
     *
     *
     * @return FormulaTable
     */
    public function scopeLastPair($query)
    {
        return $query->whereIn('id', FormulaTable::groupBy('pair')->select([DB::raw("MAX(id)")]))->orderBy('id', 'asc');
    }

    /**
     * 取得 excel 的 Spreadsheet 物件
     *
     * @return Spreadsheet
     */
    public function getSpreadsheetAttribute() : Spreadsheet
    {
        if(is_null($this->m_spreadsheet) and $this->file_path) {
            $file_location = config('filesystems.disks.' . config('admin.upload.disk') . '.root') . '/' . $this->file_path;
            $this->m_spreadsheet = IOFactory::load($file_location);
        }
        return $this->m_spreadsheet;
    }
}
