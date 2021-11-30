/**
 * Created by wangcong on 2018/06/14.
 */
function getTemplate(value){
    return '<div class="input-group" style="margin-top:10px;">' +
    '<span class="input-group-addon additem"><i class="fa fa-plus" title="添加一个节点"></i></span>' +
    '<input type="text" id="nickname" name="formdata[]" value="' + value + '" class="form-control nickname" placeholder="">' +
    '<span class="input-group-addon closeitem">' +
    '<i class="fa fa-close">' +'</i></span></div>';
}