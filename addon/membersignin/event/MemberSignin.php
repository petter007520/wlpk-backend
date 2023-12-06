<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace addon\membersignin\event;

use addon\membersignin\model\Signin;
use app\model\member\Member as MemberModel;
use app\model\member\MemberAccount as MemberAccountModel;
use app\model\member\MemberSignin as MemberSigninModel;

/**
 * 会员签到奖励
 */
class MemberSignin
{
    /**
     * @param $param
     * @return string|\multitype
     */
    public function handle($param)
    {
        $signin_model = new Signin();
        $config_result = $signin_model->getConfig($param[ 'site_id' ]);
        $config = $config_result[ 'data' ];

        $point = 0;
        $growth = 0;

        if ($config[ 'is_use' ]) {
            $member_model = new MemberModel();
            $member_signin_model = new MemberSigninModel();
            $member_account_model = new MemberAccountModel();

            // 查询当前用户连签天数
            $member_info = $member_model->getMemberInfo([ [ 'member_id', '=', $param[ 'member_id' ] ] ], 'sign_days_series,site_id')[ 'data' ];

            $award = $config[ 'value' ][ 'reward' ];

            if (!empty($award)) {
                $everyday_award = $award[ 0 ]; // 每日签到奖励
                $point = !empty($everyday_award[ 'point' ]) ? $everyday_award[ 'point' ] : 0;
                $growth = !empty($everyday_award[ 'growth' ]) ? $everyday_award[ 'growth' ] : 0;

                if (count($award) > 1) {
                    for ($i = 1; $i < count($award); $i++) {
                        $even_award = $award[ $i ]; // 连签奖励
                        if ($member_info[ 'sign_days_series' ] == $even_award[ 'day' ]) {
                            if (!empty($even_award[ 'point' ])) {
                                $point += $even_award[ 'point' ];
                            }
                            if (!empty($even_award[ 'growth' ])) {
                                $growth += $even_award[ 'growth' ];
                            }
                            break;
                        }
                    }
                }

                if ($point > 0) {
                    $remark = '签到奖励' . $point . '积分';
                    $member_account_model->addMemberAccount($param[ 'site_id' ], $param[ 'member_id' ], 'point', $point, 'signin', 0, $remark);
                }
                if ($growth > 0) {
                    $remark = '签到奖励' . $growth . '成长值';
                    $member_account_model->addMemberAccount($param[ 'site_id' ], $param[ 'member_id' ], 'growth', $growth, 'signin', 0, $remark);
                }

                // 是否已签满一个周期
                if ($member_info[ 'sign_days_series' ] == $config[ 'value' ][ 'cycle' ]) {
                    model('member')->update([ 'sign_days_series' => 0 ], [ [ 'member_id', '=', $param[ 'member_id' ] ] ]);
                }
            }
        }

        return [ 'point' => $point, 'growth' => $growth ];
    }

}