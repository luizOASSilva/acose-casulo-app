<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;

class DocumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Atos de Divulgação',
                'description' => 'DOCUMENTOS OFICIAIS E REGISTROS DE TRANSPARÊNCIA ATUALIZADOS.',
                'featured' => false,
                'order' => 1,
            ],
            [
                'name' => 'Remuneração e Contratos',
                'description' => 'DETALHAMENTO DE RECURSOS HUMANOS E CONTRATAÇÕES VIGENTES.',
                'featured' => false,
                'order' => 2,
            ],
            [
                'name' => 'Financeiro',
                'description' => 'BALANCETES, DEMONSTRATIVOS E PRESTAÇÃO DE CONTAS ANUAIS.',
                'featured' => false,
                'order' => 3,
            ],
            [
                'name' => 'Compras e Fornecedores',
                'description' => 'PROCESSOS DE AQUISIÇÃO, LICITAÇÕES E RELAÇÃO DE PARCEIROS.',
                'featured' => false,
                'order' => 4,
            ],
            [
                'name' => 'Campanhas Mensais',
                'description' => 'AÇÕES DE ARRECADAÇÃO E DESTINAÇÃO DE RECURSOS DE DOAÇÕES.',
                'featured' => false,
                'order' => 5,
            ],
            [
                'name' => 'Centro Dia',
                'description' => 'DOCUMENTAÇÃO ESPECÍFICA DA UNIDADE E ATIVIDADES REALIZADAS.',
                'featured' => true,
                'order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            DocumentCategory::query()->firstOrCreate(
                [
                    'name' => $category['name'],
                ],
                [
                    'description' => $category['description'],
                    'featured' => $category['featured'],
                    'order' => $category['order'],
                ]
            );
        }
    }
}
