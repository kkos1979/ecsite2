<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; //DBファサードの使用
use App\Mail\OrderShipped; //
use App\Rules\Tel; // カスタムバリデーションルールTelを使用。
use Gate; // Gateを使用
use Illuminate\Support\Facades\Mail; // Mailファサードの使用
use App\Http\Requests\AddressPost; // フォームリクエストによるバリデーション

class StatusController extends Controller
{
    public function index() {
      $goods = DB::table('goods')->get();
      // 管理者でログインしたら管理者ホームへリダイレクト
      if (Gate::allows('isAdmin')) {
        return redirect()->action('Admin\AdminController@index');
      } else {
        return view('index', ['goods' => $goods]);
      }
    }

    public function cartPost(Request $request) {

      $rows = [];
      $errors_over = [];
      $sum = 0;

      if (!$request->session()->has('cart')) {
        $request->session()->put('cart', []);
      }

      // リクエストからトークンを除く
      $inputs = $request->except('_token');
      // セッションに商品IDと注文数量を保存
      foreach ($inputs as $key => $value) {
        $id = str_replace('num_', '', $key);
        if (!$request->session()->has('cart.' . $id)) {
          $request->session()->put('cart.' . $id, 0);
        }
        // 注文数量の更新（もっと簡易な方法は？）
        $g_num = $request->session()->get('cart.' . $id);
        $g_num += $value;
        $request->session()->put('cart.' . $id, $g_num);
      }

      //商品情報の表示
      $cart = $request->session()->get('cart');
      foreach ($cart as $id => $num) {

        $row = DB::table('goods')->where('id', '=', $id)->first();
        if ($num !== 0) {
            $row->num = strip_tags($num);
            $sum += $num * $row->price;
            $rows[] = $row;
        }
        if (isset($row->num) && $row->num > $row->stock) {
          $errors_over[] = "{$row->name}の購入希望数が在庫数を超えています。\n購入希望数を減らしてください。";
        }
      }

      return view('/cart/cart', ['rows' => $rows, 'errors_over' => $errors_over, 'sum' => $sum]);
    }

    public function cartGet(Request $request) {

      $rows = [];
      $sum = 0;
      $errors_over = [];

      //保存したセッション情報を取得
      $cart = $request->session()->get('cart');
      foreach ($cart as $id => $num) {
        $row = DB::table('goods')->where('id', '=', $id)->first();
        if ($num !== 0) {
            $row->num = strip_tags($num);
            $sum += $num * $row->price;
            $rows[] = $row;
        }
        if (isset($row->num) && $row->num > $row->stock) {
          $errors_over[] = "{$row->name}の購入希望数が在庫数を超えています。\n購入希望数を減らしてください。";
        }
      }

      return view('/cart/cart', ['rows' => $rows, 'errors_over' => $errors_over, 'sum' => $sum]);
    }

    public function empty(Request $request) {
      // セッションcartを空にする。
      $request->session()->forget('cart');
      $sum = 0;
      return view('/cart/cart', ['sum' => $sum]);
    }

    public function buyComplete(AddressPost $request) {

      // メールアドレスが入力されている場合バリデート
      if (isset($request->email)) {
        $rules = [
          'email' => ['email'],
        ];
        $this->validate($request, $rules);
      }

      //セッションから商品情報を取得
      $cart = $request->session()->get('cart');
      $rows = [];
      $sum = 0;

      foreach ($cart as $id => $num) {
        $row = DB::table('goods')->where('id', $id)->first();
        if ($num !== 0) {
          // 商品在庫の減少
          DB::table('goods')->where('id', $id)->decrement('stock', $num);

          $row->num = $num;
          $sum += $num * $row->price;
          $rows[] = $row;
        }
      }

      if (isset($request->email)) {
        $mail_to = $request->email;
        Mail::to($mail_to)->send(new OrderShipped($request, $rows, $sum));
      }

      // セッションcartを空にする。
      $request->session()->forget('cart');
      return view('/cart/buy_complete', ['request' => $request, 'rows' => $rows, 'sum' => $sum]);
    }
}
