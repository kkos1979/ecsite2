@extends('layouts.app')

@section('title', '購入完了 | Noodle Shop')

@section('content')
  <div class="base">
    商品が購入されました。<br>
    ご購入ありがとうございました。<br>
    -----------------商品送付先------------------<br>
    お名前　：　{{ $request->name }}様<br>
    ご住所　：　{{ $request->address }}<br>
    電話番号：　{{ $request->tel }}<br>
    @foreach ($rows as $row)
    -----------------ご購入商品------------------<br>
    商品名　：{{ $row->name }}<br>
    単価　　：{{ $row->price }}円<br>
    数量　　：{{ $row->num }}<br>
    -----------------------------------------------<br>
    小計　　：{{ $row->num * $row->price }}円<br>
    @endforeach
    -----------------------------------------------<br>
    合計　　：{{ $sum }}円<br>
  </div>
  <div class="base">
    <a href="/">お買い物に戻る</a>
  </div>
@endsection
