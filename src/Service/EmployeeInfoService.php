<?php

namespace Hwacom\EIPLogin\Services;


use App\Models\HR\Employee;
use Hwacom\PersonnelInfo\Repositories\Common\EmployeeInfoRepository;
use App\Models\User;
use Hwacom\ClientSso\Services\SSOService;
use Illuminate\Support\Facades\Hash;

class EmployeeInfoService
{
    public function __construct()
    {
        $this->SSOService             = new SSOService();
        $this->EmployeeInfoRepository = new EmployeeInfoRepository();
    }

    /**
     * 同步User資料-用工號去HR資料庫抓資料回來update or create
     *
     * @param $username  *工號
     * @return mixed
     */
    public function FetchUser($username)
    {
        $depa     = null;
        $depb     = null;
        $depc     = null;
        $userData = Employee::where('EMP_ID', "$username")->first();
        if ($userData) {
            if (in_array(substr($userData->EMP_ID, 0, 2), [
                'HW',
                'HS',
                'HI',
            ])) { //substr( $userData->EMP_ID , 0 , 2 ) == 'HW'
                if ($userData->HWA_STATUS == 1) {
                    $deps      = $this->EmployeeInfoRepository->GetManagerE(69, $userData->EMP_ID);
                    $dept      = $this->EmployeeInfoRepository->DeptInfo($userData->DEP_CODE_FLOW);
                    $leader_a  = isset($dept['leader'][0]) ? $dept['leader'][0] : '';
                    $leader_b  = isset($dept['leader'][1]) ? $dept['leader'][1] : '';
                    $leader_id = User::where('enumber', $leader_a . '-' . $leader_b)->pluck('id')->first();
                    if ($dept['layer']) {//判斷處部
                        switch ($dept['layer']) {//69全部，70組，60部，50處，20總
                            case 70:
                                $depa = $deps['div']['dep_name'];
                                $depb = $deps['team']['dep_name'];
                                $depc = $deps['dept']['dep_name'];
                                break;
                            case 60:
                                $depa = $deps['div']['dep_name'] ?? $deps['gmd']['dep_name'];
                                $depb = $deps['dept']['dep_name'];
                                break;
                            case 50:
                            case 20:
                                $depa = $dept['dep_name'];
                                $depb = $dept['dep_name'];
                                break;
                        }
                    }
                } else {
                    $depa = $userData->DEP_NAME_ACT;
                    $depb = $userData->DEP_NAME_FLOW;
                }

                $user = User::UpdateOrCreate(
                    ['enumber' => $userData->EMP_ID],
                    [
                        'name'            => $userData->CNAME,
                        'email'           => $userData->EMAIL,
                        'mobile'          => $userData->MOBIL_TEL,
                        'phone_area_code' => '',
                        'phone'           => $userData->CUR_TEL,
                        'ext'             => $userData->OFC_EXT,
                        'department_a'    => $depa ?? null,
                        'department_b'    => $depb ?? null,
                        'department_c'    => $depc ?? null,
                        'position'        => $userData->TIT_NAME,
                        'dept_code'       => $userData->DEP_CODE_FLOW,
                        'leader_id'       => $leader_id ?? null,
                        'company_status'  => 0,
                        'work_status'     => $userData->HWA_STATUS,
                        'status'          => 1,
                    ]
                );

                if ($user->wasRecentlyCreated) {
                    $user->password = Hash::make($userData->EMP_ID);
                    $user->save();
                }

                if ($user->enumber == 'HW-M59' || $user->enumber == 'HW-M54' || $user->enumber == 'HW-O68') {
                    $user->detachRole('2');
                    $user->attachRole('2');
                    $user->detachRole('6');
                    $user->attachRole('6');
                }

                if ($leader_a . '-' . $leader_b == $userData->EMP_ID) {
                    if ($dept['layer'] == 50) {
                        $user->detachRole('3');
                        $user->attachRole('3');
                    } elseif ($dept['layer'] == 60) {
                        $user->detachRole('4');
                        $user->attachRole('4');
                    } elseif ($dept['layer'] == 70) {
                        $user->detachRole('5');
                        $user->attachRole('5');
                    }
                }

                if ($dept['dep_top_code'] == '0614' || $dept['dep_top_code'] == '06141') {
                    $user->detachRole('8');
                    $user->attachRole('8');
                }

                if ($dept['dep_top_code'] == '0620') {
                    $user->detachRole('7');
                    $user->attachRole('7');
                }


                if (strpos($userData->TIT_NAME, '秘書') !== false) {
                    $user->detachRole('7');
                    $user->attachRole('7');
                }

                switch ($userData->JOB_CODE) {
                    case 6:
                        $user->detachRole('6');
                        $user->attachRole('6');
                        break;
                    case 2:
                        $user->detachRole('9');
                        $user->attachRole('9');
                        break;
                }


//                //TODO:抓個資塞入worker
//                $resumes = Resume::all();
//                foreach ($resumes as $resume) {
//                    if ($resume->EMP_ID == $userData->EMP_ID){
//                        $profiles = Profile::all();
//                        foreach ($profiles as $profile) {
//                            if ($resume->ID_NO == $profile->ID_NO){
//                                Worker::UpdateOrCreate(
//                                    ['twid' => $profile->ID_NO],
//                                    [
//                                        'partner_id'        => 1,
//                                        'name'              => $profile->CNAME,
//                                        'address'           => $profile->CUR_ADDR,
//                                        'sex'               => $profile->SEX_CODE,
//                                        'birthday'          => $profile->BIRTHDAY?$profile->BIRTHDAY:null,
//                                        'email'             => $profile->EMAIL?$profile->EMAIL:null,
//                                        'mobile'            => $profile->MOBIL_TEL?$profile->MOBIL_TEL:null,
//                                        'phone'             => $profile->OFC_TEL?$profile->OFC_TEL:null,
//                                        'ext'               => $profile->OFC_EXT?$profile->OFC_EXT:null,
//                                        'department_A'      => $depa,
//                                        'department_B'      => $depb,
//                                        'department_C'      => $depc,
//                                        'title'             => $userData->TIT_NAME,
//                                        'full_time'         => 1
//                                    ]
//                                );
//                            }
//                        }
//                    }
//                }
            }
            return $user;
        }
        return false;
    }

}

