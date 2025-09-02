<?php

namespace Hwacom\PersonnelInfo\Services;

use App\Models\HR\Employee;
use Hwacom\PersonnelInfo\Repositories\Common\EmployeeInfoRepository;
use App\Models\User;
use Hwacom\ClientSso\Services\SSOService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class EmployeeInfoService
{
    protected SSOService $SSOService;
    protected EmployeeInfoRepository $EmployeeInfoRepository;

    public function __construct()
    {
        $this->SSOService             = new SSOService();
        $this->EmployeeInfoRepository = new EmployeeInfoRepository();
    }

    /**
     * 同步User資料-用工號去HR資料庫抓資料回來update or create
     *
     * @param $username  *工號
     * @param $data['ip']
     * @return mixed
     */
    public function FetchUser($username, $data)
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
                    $leader_a  = $dept['leader'][0] ?? '';
                    $leader_b  = $dept['leader'][1] ?? '';
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
                        'login_at'        => Carbon::now(),
                        'login_ip'        => $data['ip'] ?? null,
                    ]
                );

                if ($user->wasRecentlyCreated) {
                    $user->password = Hash::make($userData->EMP_ID);
                    $user->save();
                }
            }
            return $user;
        }
        return false;
    }

}
