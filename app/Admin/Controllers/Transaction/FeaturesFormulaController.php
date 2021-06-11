<?php

namespace App\Admin\Controllers\Transaction;

use App\Models\AdminUser;
use App\Models\FuturesFormula;
use Illuminate\Contracts\Support\Renderable;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use PhpOffice\PhpSpreadsheet\Writer\Html;

class FeaturesFormulaController extends AdminController implements Renderable
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'U本位合約公式表更新紀錄';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        \Encore\Admin\Admin::style('td[class^=column] { min-width: 125px; }');
        $grid = new Grid(new FuturesFormula);

        $grid->column('id', __('admin.txn.features.formula.id'))->sortable();
        $grid->column('commit', __('admin.txn.features.formula.commit'))->sortable()
                ->display(function ($commit) {
                    return nl2br($commit);
                });
        $grid->column('user_id', __('admin.txn.features.formula.user_id'))->display(function($userId) {
            return AdminUser::find($userId)->name;
        })->sortable();
        $grid->column('updated_at', __('admin.txn.features.formula.updated_at'))->date('Y-m-d H:i:s')->sortable();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('column', __('admin.txn.features.formula.id'));
            $filter->between('updated_at', __('admin.txn.features.formula.updated_at'))->datetime();
        });
        $grid->model()->orderBy('id', 'desc');
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });


        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(FuturesFormula::findOrFail($id));

        $show->field('id', __('admin.txn.features.formula.id'));
        $show->field('commit', __('admin.txn.features.formula.commit'));
        $show->field('user_id', __('admin.txn.features.formula.user_id'))->as(function ($userId) {
            return AdminUser::find($userId)->name;
        });

        $show->field('file_path', __('admin.txn.features.formula.file_path'))->file()->as(function ($render) {
            $preview = __('admin.txn.features.formula.file_preview');
            $link = route('txn.features.formula.preview', $this);
            return <<<Html
            <span>
                <a href="$link" class="btn btn-link" target="_blank">$preview <i class="fa fa-external-link"></i></a>
            </span>
            Html . $render;
        });;
        $show->divider(__('admin.txn.features.formula.divider_1'));
        $show->field('setcol1', __('admin.txn.features.formula.setcol1'));
        $show->field('setcol2', __('admin.txn.features.formula.setcol2'));
        $show->field('setcol3', __('admin.txn.features.formula.setcol3'));
        $show->field('setcol4', __('admin.txn.features.formula.setcol4'));
        $show->field('setcol5', __('admin.txn.features.formula.setcol5'));
        $show->field('setcol6', __('admin.txn.features.formula.setcol6'));
        $show->field('setcol7', __('admin.txn.features.formula.setcol7'));
        $show->field('setcol8', __('admin.txn.features.formula.setcol8'));
        $show->field('setcol9', __('admin.txn.features.formula.setcol9'));
        $show->field('setcol10', __('admin.txn.features.formula.setcol10'));
        $show->field('setcol11', __('admin.txn.features.formula.setcol11'));
        $show->divider(__('admin.txn.features.formula.divider_2'));
        $show->field('setcol12', __('admin.txn.features.formula.setcol12'));
        $show->field('setcol13', __('admin.txn.features.formula.setcol13'));
        $show->field('setcol14', __('admin.txn.features.formula.setcol14'));
        $show->divider(__('admin.txn.features.formula.divider_3'));
        $show->field('setcol15', __('admin.txn.features.formula.setcol15'));
        $show->field('setcol16', __('admin.txn.features.formula.setcol16'));
        $show->field('setcol17', __('admin.txn.features.formula.setcol17'));
        $show->field('setcol18', __('admin.txn.features.formula.setcol18'));
        $show->field('setcol19', __('admin.txn.features.formula.setcol19'));
        $show->divider(__('admin.txn.features.formula.divider_4'));
        $show->field('setcol20', __('admin.txn.features.formula.setcol20'));
        $show->field('setcol21', __('admin.txn.features.formula.setcol21'));
        $show->field('setcol22', __('admin.txn.features.formula.setcol22'));
        $show->field('setcol23', __('admin.txn.features.formula.setcol23'));
        $show->field('setcol24', __('admin.txn.features.formula.setcol24'));
        $show->field('setcol25', __('admin.txn.features.formula.setcol25'));
        $show->divider();
        $show->field('created_at', __('admin.txn.features.formula.created_at'));
        $show->field('updated_at', __('admin.txn.features.formula.updated_at'));
        $show->panel()
            ->title(__('admin.txn.features.formula.title'))
            ->tools(function ($tools) {
                $tools->disableDelete();
            });;

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FuturesFormula());

        $form->hidden('user_id', __('admin.txn.features.formula.user_id'))->value(Admin::user()->id);
        $form->column(1/2, function ($form) {
            $form->file('file_path', __('admin.txn.features.formula.file_path'))
                ->retainable()
                ->rules('mimes:xlsx')
                ->move(config('admin.upload.directory.file') . '/' . md5(uniqid()))
                ->options(['showPreview' => false]);
        });
        $form->column(1/2, function ($form) {
            $form->textarea('commit', __('admin.txn.features.formula.commit'));
        });
        $form->column(1/2, function ($form) {
            $form->divider(__('admin.txn.features.formula.divider_1'));
            $form->text('setcol1', __('admin.txn.features.formula.setcol1'))->autofocus();
            $form->text('setcol2', __('admin.txn.features.formula.setcol2'));
            $form->text('setcol3', __('admin.txn.features.formula.setcol3'));
            $form->text('setcol4', __('admin.txn.features.formula.setcol4'));
            $form->text('setcol5', __('admin.txn.features.formula.setcol5'));
            $form->text('setcol6', __('admin.txn.features.formula.setcol6'));
            $form->text('setcol7', __('admin.txn.features.formula.setcol7'));
            $form->text('setcol8', __('admin.txn.features.formula.setcol8'));
            $form->text('setcol9', __('admin.txn.features.formula.setcol9'));
            $form->text('setcol10', __('admin.txn.features.formula.setcol10'));
            $form->text('setcol11', __('admin.txn.features.formula.setcol11'));
            $form->divider(__('admin.txn.features.formula.divider_2'));
            $form->text('setcol12', __('admin.txn.features.formula.setcol12'));
            $form->text('setcol13', __('admin.txn.features.formula.setcol13'));
            $form->text('setcol14', __('admin.txn.features.formula.setcol14'));
        });
        $form->column(1/2, function ($form) {
            $form->divider(__('admin.txn.features.formula.divider_3'));
            $form->text('setcol15', __('admin.txn.features.formula.setcol15'));
            $form->text('setcol16', __('admin.txn.features.formula.setcol16'));
            $form->text('setcol17', __('admin.txn.features.formula.setcol17'));
            $form->text('setcol18', __('admin.txn.features.formula.setcol18'));
            $form->text('setcol19', __('admin.txn.features.formula.setcol19'));
            $form->divider(__('admin.txn.features.formula.divider_4'));
            $form->text('setcol20', __('admin.txn.features.formula.setcol20'));
            $form->text('setcol21', __('admin.txn.features.formula.setcol21'));
            $form->text('setcol22', __('admin.txn.features.formula.setcol22'));
            $form->text('setcol23', __('admin.txn.features.formula.setcol23'));
            $form->text('setcol24', __('admin.txn.features.formula.setcol24'));
            $form->text('setcol25', __('admin.txn.features.formula.setcol25'));
        });

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        $form->saving(function (Form $form) {

            // 檢查excel公式是否有誤
            $filepath = $form->input('file_path');
            if($filepath) {
                $path = $form->input('file_path')->path();
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $html = new Html($spreadsheet);
                $html->generateSheetData();
            }
            $form->original = $form->model()->attributesToArray();
        });

        $form->saved(function (Form $form) {
            if($form->original) {
                $row = $form->model();
                $new = new FuturesFormula();
                foreach ($row->attributesToArray() as $key => $value) {
                    if(in_array($key, ['id', 'created_at', 'updated_at']))
                        continue;
                    $new->$key = $value;
                }
                $new->user_id = Admin::user()->id;
                $new->save();

                $row->timestamps = false;
                foreach ($form->original as $key => $value) {
                    if(in_array($key, ['id', 'created_at', 'updated_at']))
                        continue;
                    $row->$key = $value;
                }
                $row->save();
            }
        });

        return $form;
    }

    public function preview(Content $content, $key = null)
    {
        \Encore\Admin\Admin::style('td[class^=column] { min-width: 125px; }');
        $html = $this->render($key);
        $box = new Box(null, $html);
        return $content
            ->title(__('admin.txn.features.formula.file_preview'))
            ->body(str_replace('box-body', 'box-body no-padding', $box));
    }

    public function render($key = null)
    {
        $formula_table = FuturesFormula::find($key);
        if($formula_table) {
            $html = new Html($formula_table->spreadsheet);
            $html = str_replace('gridlines', 'gridlines table table-bordered', $html->writeAllSheets()->generateSheetData());
            return <<<HTML
                <div class="table-responsive">$html</div>
            HTML;
        }
        return "No Data";
    }
}
