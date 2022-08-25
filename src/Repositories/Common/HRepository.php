<?php

namespace App\Repositories\Common;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Arr;

class HRepository extends Model
{
    protected $user;

    public function __construct() // User $user
    {
        // $this->user = $user;
    }

    public static function DeptName($dept){
        $query = 'SELECT DEP_NAME_ACT,DEP_NAME,DEPT_BOSS,DEP_CODE_FLOW FROM ORG_DEPART_V WHERE IS_USE = "Y" AND DEP_CODE = '."'".$dept."'";

        $deps = \DB::connection('hr')->select(\DB::raw($query));

        if (isset($deps[0])){

        }

        return $deps;
    }

    public static function DeptInfo($dept){
        $query = 'SELECT DEP_NAME_ACT,DEP_NAME,DEPT_BOSS,DEP_CODE_FLOW,LAYER_CODE FROM ORG_DEPART_V WHERE IS_USE = "Y" AND DEP_CODE = '."'".$dept."'";

        $deps = \DB::connection('hr')->select(\DB::raw($query));

        if (isset($deps[0])){
            $dept_top = self::DeptName($deps[0]->DEP_CODE_FLOW);

            $data = [
                'leader'        => explode('-',$deps[0]->DEPT_BOSS),
                'dep_name'      => $deps[0]->DEP_NAME,
                'dep_top'       => isset($dept_top[0])?$dept_top[0]->DEP_NAME:'',
                'dep_top_code'  => $deps[0]->DEP_CODE_FLOW,
                'layer'         => $deps[0]->LAYER_CODE
            ];
        }else{
            return '';
        }
        return $data;
    }

    /**
     * 取得處別資訊 組:虛擬(Flow) 部:實體(ACT)
     *
     * @param $dept
     * @return array|string
     */
    public static function getDeptInfo($dept){
        $query = 'SELECT DEP_NAME_ACT,DEP_NAME,DEPT_BOSS,DEP_CODE_FLOW,DEP_CODE_ACT,LAYER_CODE FROM ORG_DEPART_V WHERE IS_USE = "Y" AND DEP_CODE = '."'".$dept."'";

        $deps = \DB::connection('hr')->select(\DB::raw($query));

        if (isset($deps[0])){
            if($deps[0]->LAYER_CODE == 70){
                $dept_top = self::DeptName($deps[0]->DEP_CODE_FLOW);

                $data = [
                    'leader'        => explode('-',$deps[0]->DEPT_BOSS),
                    'dep_name'      => $deps[0]->DEP_NAME,
                    'dep_top'       => isset($dept_top[0])?$dept_top[0]->DEP_NAME:'',
                    'dep_top_code'  => $deps[0]->DEP_CODE_FLOW,
                    'layer'         => $deps[0]->LAYER_CODE
                ];
            }else{
                $dept_top = self::DeptName($deps[0]->DEP_CODE_ACT);

                $data = [
                    'leader'        => explode('-',$deps[0]->DEPT_BOSS),
                    'dep_name'      => $deps[0]->DEP_NAME,
                    'dep_top'       => isset($dept_top[0])?$dept_top[0]->DEP_NAME:'',
                    'dep_top_code'  => $deps[0]->DEP_CODE_ACT,
                    'layer'         => $deps[0]->LAYER_CODE
                ];
            }

        }else{
            return [];
        }

        return $data;
    }

    public static function GetTroops($empid){
        $data = null;
        $query = 'SELECT EMP_ID,DEP_CODE_FLOW FROM HWA_EMP_PROFILE WHERE EMP_ID = '."'".$empid."'";
        $emp = \DB::connection('hr')->select(\DB::raw($query));
        if (isset($emp[0])){
            $dept = self::DeptInfo($emp[0]->DEP_CODE_FLOW);
        }

        if (isset($dept['leader'])){
            if ($dept['leader'][0].'-'.$dept['leader'][1] == $empid){
                $query = 'SELECT DEP_CODE,DEP_NAME_FLOW,DEP_NAME,DEPT_BOSS,DEP_CODE_FLOW,LAYER_CODE FROM ORG_DEPART_V WHERE IS_USE = "Y" AND DEP_CODE_FLOW = '."'".$emp[0]->DEP_CODE_FLOW."'";
                $troops = \DB::connection('hr')->select(\DB::raw($query));

                return $troops;
            }
        }

        return false;
    }

    //用工號搜尋底下部門(回傳陣列)
    public function GetManagers($type,$empid){//type = DEP_NAME 或 DER_CODE
        $manage = [];

        $query = 'SELECT ORG_DEPART_V.'.$type.' FROM HWA_EMP_PROFILE JOIN ORG_DEPART_V ON HWA_EMP_PROFILE.PER_SERIL_NO = ORG_DEPART_V.BOSS_SERIL_NO WHERE ORG_DEPART_V.LAYER_CODE <> "55" AND HWA_EMP_PROFILE.EMP_ID = '."'".$empid."'";

        $datas = \DB::connection('hr')->select(\DB::raw($query));

        foreach ($datas as $data) {
            $manage[] = $data->$type;
        }

        return $manage;
    }

    //用工號搜尋底下部門
    public function GetManagerE($layer,$empid){//69全部，70組，60部，50處，20總
        $data = [];
        $dept = null;
        $query = 'SELECT EMP_ID,DEP_CODE_FLOW FROM HWA_EMP_PROFILE WHERE EMP_ID = '."'".$empid."'";

        $emp = \DB::connection('hr')->select(\DB::raw($query));

        if (isset($emp[0])){
            $dept = $emp[0]->DEP_CODE_FLOW;
        }

        $query = 'SELECT DEP_NAME_FLOW,DEP_CODE,DEP_NAME,DEPT_BOSS,DEP_CODE_FLOW,LAYER_CODE FROM ORG_DEPART_V WHERE IS_USE = "Y" AND DEP_CODE = '."'".$dept."'";

        $deps = \DB::connection('hr')->select(\DB::raw($query));
        //dd($deps[0]);
        $i = 1;

        if ($layer == 69){
            while(isset($deps[0])){
                $leader = explode('-',$deps[0]->DEPT_BOSS);
                $leader_a = isset($leader[0])?$leader[0].'-':'';
                $leader_b = isset($leader[1])?$leader[1]:'';
                if ($deps[0]->LAYER_CODE != 55 && $deps[0]->LAYER_CODE != 54 && $deps[0]->LAYER_CODE != 10){
                    $lnm = $this->getLayerName($deps[0]->LAYER_CODE);
                    $data[$lnm] =[
                        'leader'        => $leader_a.$leader_b,
                        'leader_name'   => $deps[0]->DEPT_BOSS,
                        'dep_name'      => $deps[0]->DEP_NAME,
                        'dep_code'      => $deps[0]->DEP_CODE,
                        'layer_code'    => $deps[0]->LAYER_CODE,
                        'dep_top'       => $deps[0]->DEP_NAME_FLOW,
                        'dep_top_code'  => $deps[0]->DEP_CODE_FLOW,
                    ];
                }

                $query = 'SELECT DEP_NAME_FLOW,DEP_NAME,DEPT_BOSS,DEP_CODE_FLOW,LAYER_CODE,DEP_CODE FROM ORG_DEPART_V WHERE IS_USE = "Y" AND DEP_CODE = '."'".$deps[0]->DEP_CODE_FLOW."'";
                $deps = \DB::connection('hr')->select(\DB::raw($query));

                $i++;
            }
        }else{
            while(isset($deps[0])){

                $leader = explode('-',$deps[0]->DEPT_BOSS);
                $leader_a = isset($leader[0])?$leader[0].'-':'';
                $leader_b = isset($leader[1])?$leader[1]:'';
                if ($deps[0]->LAYER_CODE == $layer){
                    $data =[
                        'leader'        => $leader_a.$leader_b,
                        'leader_name'   => $deps[0]->DEPT_BOSS,
                        'dep_name'      => $deps[0]->DEP_NAME,
                        'dep_code'      => $deps[0]->DEP_CODE,
                        'layer_code'    => $deps[0]->LAYER_CODE,
                        'dep_top'       => $deps[0]->DEP_NAME_FLOW,
                        'dep_top_code'  => $deps[0]->DEP_CODE_FLOW,
                    ];
                    break;
                }

                $query = 'SELECT DEP_NAME_FLOW,DEP_NAME,DEPT_BOSS,DEP_CODE_FLOW,LAYER_CODE,DEP_CODE FROM ORG_DEPART_V WHERE IS_USE = "Y" AND DEP_CODE = '."'".$deps[0]->DEP_CODE_FLOW."'";
                $deps = \DB::connection('hr')->select(\DB::raw($query));

                $i++;
            }
        }
        return $data;
    }

    //用User ID 搜尋底下部門
    public function GetManager($layer,$id){//69全部，80子公司組類，70組，60部，50處，20總
        $data = null;
        $user = User::find($id);

        $query = 'SELECT EMP_ID,DEP_CODE_FLOW FROM HWA_EMP_PROFILE WHERE EMP_ID = '."'".$user->enumber."'";

        $emp = \DB::connection('hr')->select(\DB::raw($query));
        if (isset($emp[0])){
            $dept = $emp[0]->DEP_CODE_FLOW;
        }

        $query = 'SELECT DEP_CODE,DEP_NAME_FLOW,DEP_NAME,DEPT_BOSS,DEP_CODE_FLOW,LAYER_CODE FROM ORG_DEPART_V WHERE IS_USE = "Y" AND DEP_CODE = '."'".$dept."'";

        $deps = \DB::connection('hr')->select(\DB::raw($query));
        //dd($deps[0]);
        $i = 1;

        if ($layer == 69){
            while(isset($deps[0])){
                if (!empty($deps[0]->DEPT_BOSS)){
                    $leader = explode('-',$deps[0]->DEPT_BOSS);
                    $leader_a = isset($leader[0])?$leader[0].'-':'';
                    $leader_b = isset($leader[1])?$leader[1]:'';
                    if ($deps[0]->LAYER_CODE != 55 && $deps[0]->LAYER_CODE != 54 && $deps[0]->LAYER_CODE != 10){
                        $lnm = $this->getLayerName($deps[0]->LAYER_CODE);
                        $data[$lnm] =[
                            'dep_code'      => $deps[0]->DEP_CODE,
                            'leader'        => $leader_a.$leader_b,
                            'leader_name'   => $deps[0]->DEPT_BOSS,
                            'dep_name'      => $deps[0]->DEP_NAME,
                            'layer_code'    => $deps[0]->LAYER_CODE,
                            'dep_top'       => $deps[0]->DEP_NAME_FLOW,
                            'dep_top_code'  => $deps[0]->DEP_CODE_FLOW,
                        ];
                    }

                    $query = 'SELECT DEP_CODE,DEP_NAME_FLOW,DEP_NAME,DEPT_BOSS,DEP_CODE_FLOW,LAYER_CODE FROM ORG_DEPART_V WHERE IS_USE = "Y" AND DEP_CODE = '."'".$deps[0]->DEP_CODE_FLOW."'";
                    $deps = \DB::connection('hr')->select(\DB::raw($query));

                    $i++;
                }
            }
        }else{
            while(isset($deps[0])){

                $leader = explode('-',$deps[0]->DEPT_BOSS);
                $leader_a = isset($leader[0])?$leader[0].'-':'';
                $leader_b = isset($leader[1])?$leader[1]:'';
                if ($deps[0]->LAYER_CODE == $layer){

                    $data =[
                        'dep_code'      => $deps[0]->DEP_CODE,
                        'leader'        => $leader_a.$leader_b,
                        'leader_name'   => $deps[0]->DEPT_BOSS,
                        'dep_name'      => $deps[0]->DEP_NAME,
                        'layer_code'    => $deps[0]->LAYER_CODE,
                        'dep_top'       => $deps[0]->DEP_NAME_FLOW,
                        'dep_top_code'  => $deps[0]->DEP_CODE_FLOW,
                    ];
                    break;
                }

                $query = 'SELECT DEP_CODE,DEP_NAME_FLOW,DEP_NAME,DEPT_BOSS,DEP_CODE_FLOW,LAYER_CODE FROM ORG_DEPART_V WHERE IS_USE = "Y" AND DEP_CODE = '."'".$deps[0]->DEP_CODE_FLOW."'";
                $deps = \DB::connection('hr')->select(\DB::raw($query));

                $i++;
            }
        }



        return $data;

    }

    private function getLayerName($layer){
        $res = null;
        switch ($layer){
            case 20:
                $res = 'gmd';
                break;
            case 50:
                $res = 'div';
                break;
            case 60:
                $res = 'dept';
                break;
            case 70:
                $res = 'team';
                break;
            case 80:
                $res = 'sub-team';
                break;
        }

        return $res;
    }

    public function GetEmployee($enumber){
        $data = null;
        $query = 'SELECT * FROM HWA_EMP_PROFILE WHERE EMP_ID = '."'".$enumber."'";
        $employee = \DB::connection('hr')->select(\DB::raw($query));

        return $employee?$employee[0]:null;
    }

    /**
     * 取得所有組織
     *
     * @return mixed
     */
    public function GetAllDep()
    {
        //80子公司組類，70組，60部，50處，20總
        $arr_layers = ['80', '70', '60', '50', '20'];
        $str_layers = implode(',', $arr_layers);
        $query = "SELECT DEP_NAME_ACT,DEP_NAME_FLOW,DEP_CODE,DEP_NAME,DEPT_BOSS,DEP_CODE_ACT,DEP_CODE_FLOW,LAYER_CODE FROM ORG_DEPART_V WHERE IS_USE = 'Y' AND LAYER_CODE IN ({$str_layers}) ORDER BY LAYER_CODE";
        $deps  = \DB::connection('hr')->select(\DB::raw($query));
        return $deps;
    }
}
