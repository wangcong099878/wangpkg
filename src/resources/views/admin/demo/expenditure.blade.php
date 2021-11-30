<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2020/9/9
 * Time: 2:07 下午
 */
?>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-body" style="display:block;">
                <div class="form-group">
                    <label>时间范围</label>
                    <div class="input-group" style="width:350px;">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <input type="text" class="form-control" id="start_time" placeholder="开始时间" name="start"
                               value="{{$start_time}}">
                        <span class="input-group-addon" style="border-left: 0; border-right: 0;">-</span>
                        <input type="text" class="form-control" id="end_time" placeholder="结束时间" name="end"
                               value="{{$end_time}}">
                    </div>

                    <div class="input-group" style="width:300px;padding-top: 10px;">
                        <a class="btn btn-sm btn-dropbox" href="#" id="showData" role="button">查看</a>
                    </div>
                </div>

                <div class="row">
                    <table class="table ">
                        <thead>
                        <tr>
                            <th>对外支出</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <span id="showTbody">0</span>元 &nbsp;
                                <a href="/{{config('admin.route.prefix')}}/accountDetail?&type=1"
                                   target="_blank" class="btn btn-primary btn-xs">查看明细</a>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>
            var prefixPath = '/{{config('admin.route.prefix')}}';
            var token = '{{$token}}';
            $(function () {
                $('#start_time').datetimepicker({"format": "YYYY-MM-DD HH:mm:ss", "locale": "zh-CN"});
                $('#end_time').datetimepicker({
                    "format": "YYYY-MM-DD HH:mm:ss",
                    "locale": "zh-CN",
                    "useCurrent": false
                });

                $("#start_time").on("dp.change", function (e) {
                    $('#end_time').data("DateTimePicker").minDate(e.date);
                });

                $("#end_time").on("dp.change", function (e) {
                    $('#start_time').data("DateTimePicker").maxDate(e.date);
                });

                $('#showData').click(function () {
                    showData();

                });

                showData();

            });

            function showData() {
                var start_time = $('#start_time').val();
                var end_time = $('#end_time').val();

                if (!start_time) {
                    alert('开始时间不可为空');
                    return;
                }
                if (!end_time) {
                    alert('结束不可为空');
                    return;
                }
                var req = {
                    start_time: start_time,
                    end_time: end_time,
                    _token: token
                }

                $.post(prefixPath + '/statistics/expenditure', req, function (res) {
                    toastr.success("请求成功");
                    $('#showTbody').html(res.data);
                }, 'json');
            }
        </script>
    </div>
</div>

