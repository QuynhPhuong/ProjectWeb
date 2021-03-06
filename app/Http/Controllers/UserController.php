<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\User;

class UserController extends Controller
{
    //
    public function getDanhsach(){
    	$user = User::all();
    	return view('admin.user.danhsach',['user'=>$user]);
    }
    public function getThem(){
        return view('admin.user.them');
    }

    public function postThem(Request $request){
        //echo $request->Ten; //Ten nay la name cua tag input
        $this->validate($request,
            [
                'name' => 'required|min:3',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:3|max:32',
                'passwordAgain' => 'required|same:password',
            ],[
                'name.required' => 'Bạn chưa nhập tên',
                'name.min' => 'Tên người dùng phải có ít nhất 3 ký tự',
                'email.required' => 'Bạn chưa nhập email',
                'email.email' => 'Bạn chưa nhập đúng định dạng email',
                'email.unique' => 'Email đã tồn tại',
                'password.required' => 'Bạn chưa nhập mật khẩu',
                'password.min' => 'Mật khẩu phải có độ dài từ 3 đến 32 ký tự',
                'password.max' => 'Mật khẩu phải có độ dài từ 3 đến 32 ký tự',
                'passwordAgain.required' => 'Bạn chưa nhập lại mật khẩu',
                'passwordAgain.same' => 'Mật khẩu nhập lại không đúng',
            ]);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->level = $request->level;
        $user->status = $request->status;

        $user->save();

        return redirect('admin/user/them')->with('thongbao','Thêm thành công'); // gán thêm session thongbao
    }
    public function getSua($id){
        $user = User::find($id);
        return view('admin.user.sua',[
            'user'=>$user
            ]);
    }

    public function postSua(Request $request,$id){
        
        $this->validate($request,
            [
                'name' => 'required|min:3',
            ],[
                'name.required' => 'Bạn chưa nhập tên',
                'name.min' => 'Tên người dùng phải có ít nhất 3 ký tự',
            ]);

        $user = User::find($id);
        $user->name = $request->name;
        $user->level = $request->level;
        $user->status = $request->status;

        if($request->changePassword == "on"){
            $this->validate($request,
            [
                'password' => 'required|min:3|max:32',
                'passwordAgain' => 'required|same:password',
            ],[
                'password.required' => 'Bạn chưa nhập mật khẩu',
                'password.min' => 'Mật khẩu phải có độ dài từ 3 đến 32 ký tự',
                'password.max' => 'Mật khẩu phải có độ dài từ 3 đến 32 ký tự',
                'passwordAgain.required' => 'Bạn chưa nhập lại mật khẩu',
                'passwordAgain.same' => 'Mật khẩu nhập lại không đúng',
            ]);
            $user->password = bcrypt($request->password);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $duoi = $file->getClientOriginalExtension();
            if ($duoi != 'jpg' && $duoi != 'png' && $duoi != 'jpeg') {
                return redirect('admin/user/sua')->with('loi','Bạn chỉ được chọn file có đuôi jpg,jpeg,png');
            }
            $name = $file->getClientOriginalName();
            $avatar = str_random(4)."_".$name;
            while (file_exists("upload/user/".$avatar)) {
                $avatar = str_random(4)."_".$name;
            }
            $file->move("upload/user",$avatar);
            if ($user->avatar != "") {
                unlink("upload/user/".$user->avatar);
            }
            $user->avatar = $avatar;
        }


        $user->save();

        return redirect('admin/user/sua/'.$id)->with('thongbao','Sửa thành công'); // gán thêm session thongbao
    }
    public function getXoa($id){
        $user = User::find($id);
        $user->delete();

        return redirect('admin/user/danhsach')->with('thongbao','Xóa thành công');
    }
    public function getDangnhapAdmin(){
    	return view('admin.login');
    }

    public function postDangnhapAdmin(Request $request){
    	$this->validate($request,
    		[
                'email' => 'required',
                'password' => 'required',
    		],[
                'email.required' => 'Bạn chưa nhập email',
    			'password.required' => 'Bạn chưa nhập mật khẩu',
    		]);

    	if(Auth::attempt(['email'=>$request->email,'password'=>$request->password])){
    		return redirect('admin/trangchu');
    	}else{
    		return redirect('admin/dangnhap')->with('thongbao','Đăng nhập không thành công');
    	}
    }

    public function getDangxuatAdmin(){
        Auth::logout();
        return redirect('admin/dangnhap');
    }
}
