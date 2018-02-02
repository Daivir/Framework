<?php

use Phinx\Seed\AbstractSeed;

class ProductSeeder extends AbstractSeed
{
    public function run()
    {
        $faker = \Faker\Factory::create('fr_FR');
        $data = [];
        for ($i = 0; $i < 20; $i++) {
            $date = $faker->unixTime('now');
            $data[] = [
                'name' => $faker->catchPhrase,
                'slug' => $faker->slug,
                'description' => $faker->text(3000),
                'price' => $faker->randomFloat(2, 9.99, 99.99),
                'image' => 'fake.jpg',
                'created_at' => date('Y-m-d H:i:s', $date),
                'updated_at' => date('Y-m-d H:i:s', $date)
            ];
        }
        $this->table('products')->insert($data)->save();
    }
}
