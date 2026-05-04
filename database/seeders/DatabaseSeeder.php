<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Article;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Keyword;
use App\Models\Activity;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::factory()->create([
            'name'  => 'Admin Test',
            'email' => 'admin@test.com',
        ]);

        $keywords = Keyword::factory(10)->create();

        Article::factory(5)
            ->create()
            ->each(function ($article) use ($keywords) {
                $article->keywords()->attach(
                    $keywords->random(rand(1, 4))->pluck('id')
                );
            });

        Activity::factory(15)->create();

        $categories = DocumentCategory::factory()->createMany([
            [
                'name' => 'Atos de Divulgação',
                'description' => 'DOCUMENTOS OFICIAIS E REGISTROS DE TRANSPARÊNCIA ATUALIZADOS.',
                'featured' => false,
                'order' => 1
            ],
            [
                'name' => 'Remuneração e Contratos',
                'description' => 'DETALHAMENTO DE RECURSOS HUMANOS E CONTRATAÇÕES VIGENTES.',
                'featured' => false,
                'order' => 2
            ],
            [
                'name' => 'Financeiro',
                'description' => 'BALANCETES, DEMONSTRATIVOS E PRESTAÇÃO DE CONTAS ANUAIS.',
                'featured' => false,
                'order' => 3
            ],
            [
                'name' => 'Compras e Fornecedores',
                'description' => 'PROCESSOS DE AQUISIÇÃO, LICITAÇÕES E RELAÇÃO DE PARCEIROS.',
                'featured' => false,
                'order' => 4
            ],
            [
                'name' => 'Campanhas Mensais',
                'description' => 'AÇÕES DE ARRECADAÇÃO E DESTINAÇÃO DE RECURSOS DE DOAÇÕES.',
                'featured' => false,
                'order' => 5
            ],
            [
                'name' => 'Centro Dia',
                'description' => 'DOCUMENTAÇÃO ESPECÍFICA DA UNIDADE E ATIVIDADES REALIZADAS.',
                'featured' => true,
                'order' => 6
            ],
        ]);

        foreach ($categories as $category) {
            foreach ([2024, 2025, 2026] as $year) {
                Document::factory(2)->create([
                    'admin_id'    => $admin->id,
                    'category_id' => $category->id,
                    'year'        => $year,
                ]);
            }
        }
    }
}
