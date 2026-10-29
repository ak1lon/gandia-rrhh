<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legajos', function (Blueprint $table) {
            $table->unsignedInteger('leg_numero')->primary();
            $table->string('leg_apellido', 50)->nullable();
            $table->string('leg_nombre', 50)->nullable();
            $table->string('leg_apecas', 30)->nullable();
            $table->string('leg_foto', 255)->nullable();
            $table->unsignedTinyInteger('leg_tipodoc')->nullable();
            $table->string('leg_numdoc', 9)->nullable()->index();
            $table->date('leg_fecnac')->nullable();
            $table->unsignedTinyInteger('leg_sexo')->nullable();
            $table->string('est_codigo', 3)->nullable();
            $table->string('t01_codigo', 3)->nullable();
            $table->string('leg_cuil', 11)->nullable();
            $table->boolean('leg_discap')->default(false);
            $table->string('leg_domic', 30)->nullable();
            $table->string('leg_puerta', 5)->nullable();
            $table->string('leg_piso', 2)->nullable();
            $table->string('leg_depto', 4)->nullable();
            $table->string('leg_codpos', 8)->nullable();
            $table->string('leg_locali', 60)->nullable();
            $table->string('leg_prvdom', 3)->nullable();
            $table->string('leg_telefono', 30)->nullable();
            $table->boolean('leg_pertemp')->default(false);
            $table->string('ese_codigo', 3)->nullable();
            $table->date('leg_fecingt')->nullable();
            $table->date('leg_fecegrt')->nullable();
            $table->date('leg_fecing')->nullable();
            $table->date('leg_fecegr')->nullable();
            $table->date('leg_fecingr')->nullable();
            $table->string('meg_codigo', 3)->nullable();
            $table->string('cat_codigo', 3)->nullable();
            $table->string('sec_codigo', 3)->nullable();
            $table->string('car_codigo', 3)->nullable();
            $table->string('lpg_codigo', 6)->nullable();
            $table->string('leg_prvtra', 3)->nullable();
            $table->unsignedTinyInteger('leg_clase')->nullable();
            $table->decimal('leg_basico', 15, 2)->nullable();
            $table->string('leg_nrocons', 12)->nullable();
            $table->string('leg_ctafon', 12)->nullable();
            $table->string('t02_codigo', 3)->nullable();
            $table->unsignedTinyInteger('leg_fpago')->nullable();
            $table->string('bco_codigo', 3)->nullable();
            $table->string('bco_sucursal', 4)->nullable();
            $table->string('bco_codigo_NC', 3)->nullable();
            $table->string('bco_sucursal_NC', 4)->nullable();
            $table->string('leg_ctaban', 50)->nullable();
            $table->string('leg_tipcuen', 1)->nullable();
            $table->string('sin_codigo', 3)->nullable();
            $table->string('afjp_codigo', 3)->nullable();
            $table->string('tme_codigo', 3)->nullable();
            $table->string('oso_codigo', 3)->nullable();
            $table->boolean('leg_salfam')->default(false);
            $table->unsignedTinyInteger('act_codigo')->nullable();
            $table->string('mpr_codigo', 3)->nullable();
            $table->date('leg_fecvto')->nullable();
            $table->date('leg_fecdes')->nullable();
            $table->date('leg_fechas')->nullable();
            $table->string('leg_tiponov', 1)->nullable();
            $table->decimal('leg_porcadi', 8, 2)->nullable();
            $table->string('leg_obsrec', 255)->nullable();
            $table->boolean('leg_confidencial')->default(false);
            $table->boolean('leg_regsimp')->default(false);
            $table->string('usu_codigo', 15)->nullable();
            $table->date('leg_fecmod')->nullable();
            $table->unsignedTinyInteger('sinies_Codigo')->nullable();
            $table->unsignedTinyInteger('leg_Cbu1')->nullable();
            $table->unsignedTinyInteger('leg_Cbu2')->nullable();
            $table->boolean('leg_convencionado')->default(false);
            $table->unsignedTinyInteger('T02_Codigo1')->nullable();
            $table->unsignedTinyInteger('T02_Codigo2')->nullable();
            $table->unsignedTinyInteger('T02_Codigo3')->nullable();
            $table->unsignedTinyInteger('Leg_dia1')->nullable();
            $table->unsignedTinyInteger('Leg_dia2')->nullable();
            $table->unsignedTinyInteger('Leg_dia3')->nullable();
            $table->string('Leg_torre', 30)->nullable();
            $table->string('Leg_sector', 30)->nullable();
            $table->string('Leg_escalera', 30)->nullable();
            $table->string('Leg_entrecalles', 50)->nullable();
            $table->string('Leg_celular', 30)->nullable();
            $table->string('dva_codigo', 6)->nullable();
            $table->string('Leg_mail', 150)->nullable();
            $table->boolean('leg_Agro')->default(false);
            $table->date('leg_FecTelegRenun')->nullable();
            $table->string('dex_Codigo', 6)->nullable();
            $table->string('pue_codigo', 4)->nullable();
            $table->string('nest_codigo', 2)->nullable();
            $table->string('leg_codArea', 6)->nullable();
            $table->string('ttel_codigo', 1)->nullable();
            $table->string('tmail_codigo', 1)->nullable();
            $table->unsignedSmallInteger('loc_codigo')->nullable();
            $table->boolean('leg_presentado')->default(false);
            $table->unsignedSmallInteger('leg_sitMs')->nullable();
            $table->string('ale_codigo', 6)->nullable();
            $table->string('leg_tipcuenMS', 2)->nullable();
            $table->string('leg_afiliado', 1)->nullable();
            $table->unsignedTinyInteger('leg_convenio')->nullable();
            $table->unsignedTinyInteger('leg_categoria')->nullable();
            $table->string('Leg_AdmPubProv', 1)->nullable();
            $table->string('leg_CIexpedida', 100)->nullable();
            $table->string('leg_depdgi', 100)->nullable();
            $table->string('leg_barrio', 63)->nullable();
            $table->string('leg_relusu', 20)->nullable();
            $table->boolean('leg_SegCVO')->default(false);
            $table->string('cpf_codigo', 3)->nullable();
            $table->unsignedTinyInteger('sit_codigo')->nullable();
            $table->unsignedTinyInteger('cond_codigo')->nullable();
            $table->unsignedTinyInteger('leg_cat_camion')->nullable();
            $table->boolean('leg_hab_f572')->default(false);
            $table->string('leg_CBU', 60)->nullable();
            $table->boolean('leg_ApliTopeLiqFinal')->default(false);
            $table->unsignedInteger('leg_numeroManager')->nullable();
            $table->unsignedTinyInteger('leg_informaCantDiasTrab')->nullable();
            $table->date('leg_fecCvd19HsExtDsd')->nullable();
            $table->date('leg_fecCvd19GrdDsd')->nullable();
            $table->date('leg_fecCvd19AdicDsd')->nullable();
            $table->date('leg_fecCvd19HsExtHst')->nullable();
            $table->date('leg_fecCvd19GrdHst')->nullable();
            $table->date('leg_fecCvd19AdicHst')->nullable();
            $table->unsignedTinyInteger('leg_Cvd19HsExtra')->nullable();
            $table->unsignedTinyInteger('leg_Cvd19Guardias')->nullable();
            $table->unsignedTinyInteger('leg_Cvd19Adic')->nullable();
            $table->unsignedTinyInteger('Leg_TipoJornal')->nullable();
            $table->string('cct_codigo', 4)->nullable();
            $table->unsignedTinyInteger('leg_cat_feder')->nullable();
            $table->boolean('leg_AplicaDetraccion')->default(false);
            $table->boolean('leg_Trasnsporteld')->default(false);
            $table->boolean('leg_Transporteldt')->default(false);
            $table->boolean('leg_NoAplicaDetraccion')->default(false);
            $table->boolean('leg_GanRegCed')->default(false);
            $table->boolean('leg_GanRegGral')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legajos');
    }
};

