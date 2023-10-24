<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\pedidos;
use App\Models\clientes;
use App\Models\produtos;


class PedidosController extends Controller
{
    public function pedidos(){

        return view('pedidos.pedidos');
    }    

    public function cadastropedido(){
        return view('pedidos.cadastropedido');
    }

    public function store(Request $request){
        $emailCliente = $request->input('email-cliente');

        $cliente = clientes::where('email', $emailCliente)->first();

        if(!$cliente) {
            //cliente não encontrado
            $error404 = "Client not found";
            abort(404, $error404);

        } else {
            $nomeProduto = $request->input('produto');
            $produtos = produtos::where('nome_produto', $nomeProduto)->first();

            if(!$produtos){
                //produto nao encontrado
                $error404 = "Produto not found";
                abort(404, $error404);    

            } else {
                $quantidade = $request->input('quantidade');
                $valor_produto = $produtos->valor_produto;

                $valor_pedido = $valor_produto * $quantidade;
                $pedido = pedidos::create(
                    [
                        'id_cliente' => $cliente->id_cliente,
                        'id_produto' => $produtos->id_produto,
                        'id_forma_pagamento' => $request->input('id_forma_pagamento'),
                        'valor_pedido' => $valor_pedido,
                        'status_pedido' => $request->input('status_pedido'),
                        'status_pagamento' => $request->input('status_pagamento'),
                        'quantidade' => $quantidade
                    ]);
                return redirect()->route('pedidos');
            }
        }
    }

    public function listarpedidos(){
        $pedidos = pedidos::all(); // recuperando parametros da tabela

        $formasPagamento = [
            1 => 'Pix',
            2 => 'Cartão de Crédito',
            3 => 'Cartão de Débito',
            4 => 'Dinheiro Físico',
        ];
        
        return view('pedidos.pedidos', ['pedidos' => $pedidos, 'formasPagamento' => $formasPagamento]);
    }

    public function edit($id_pedido){
        $pedidos = pedidos::find($id_pedido);
        return view('pedidos.editpedido',['pedidos' => $pedidos]);
    }

    public function update(Request $request, $id_pedido){
        $error404 = "NOT FOUND";
        $pedidos = pedidos::find($id_pedido);
        $emailCliente = $request->input('email-cliente');
        $clientes = clientes::where('email', $emailCliente)->first();
        
        if(!$clientes){
            abort(404, $error404);
        } else {
            $nomeProduto = $request->input('produto');
            $produtos = produtos::where('nome_produto', $nomeProduto)->first();

            if(!$produtos) {
                abort(404, $error404);
            } else {
                $quantidade = $request->input('quantidade');
                $valor_produto = $pedidos->produtos->valor_produto;
    
                $valor_pedido = $valor_produto * $quantidade;
    
                $pedidos->id_cliente = $clientes->id_cliente;
                $pedidos->id_produto = $produtos->id_produto;
                $pedidos->id_forma_pagamento = $request->input('id_forma_pagamento');
                $pedidos->valor_pedido = $valor_pedido;
                $pedidos->status_pagamento = $request->input('status_pagamento');
                $pedidos->status_pedido = $request->input('status_pedido');
                $pedidos->quantidade = $quantidade;
            
                $pedidos->save();
                return redirect()->route('pedidos');
            }
        }
        
    }

    public function delete($id_pedido){
        $pedidos = pedidos::find($id_pedido);
        if($pedidos){
            $pedidos->delete();
          return redirect()->route('pedidos');
        } else {
          return redirect()->route('pedidos');
        }
      }

      public function search(Request $request){

        $formasPagamento = [
            1 => 'Pix',
            2 => 'Cartão de Crédito',
            3 => 'Cartão de Débito',
            4 => 'Dinheiro Físico',
        ];

        $search = $request->search;

            $pedidos = pedidos::where(function ($query) use ($search) {
                $query->where('status_pedido', 'like', '%' . $search . '%')
                      ->orWhere('id_pedido', 'like', '%' . $search . '%')
                      ->orWhere('quantidade', 'like', '%' . $search . '%')
                      ->orWhere('status_pagamento', 'like', '%' . $search . '%')
                      ->orWhere('id_forma_pagamento', 'like', '%' . $search . '%')
                      ->orWhere('valor_pedido', 'like', '%' . $search . '%');
            })->orWhereHas('clientes', function ($query) use ($search) {
                $query->where('nome_cliente', 'like', '%' . $search . '%');
            })->orWhereHas('produtos', function ($query) use ($search) {
                $query->where('nome_produto', 'like', '%' . $search . '%');
            })->get();

        return view ('pedidos.search', compact('pedidos', 'formasPagamento'));
      }
}
