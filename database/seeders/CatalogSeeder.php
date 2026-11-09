<?php

namespace Database\Seeders;

use App\Models\CatalogProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** 50 produits de mode (noms fictifs). Idempotent : ne modifie jamais un produit déjà en base. */
class CatalogSeeder extends Seeder
{
    private const VETEMENT = ['S', 'M', 'L', 'XL'];

    private const CHAUSSURE = ['40', '41', '42', '43', '44'];

    private const UNIQUE = ['Unique'];

    public function run(): void
    {
        foreach ($this->products() as [$name, $description, $euros, $sizes]) {
            $product = CatalogProduct::firstOrCreate(
                ['name' => $name],
                ['description' => $description, 'price_cents' => $euros * 100, 'sizes' => $sizes],
            );

            // La photo est rattachée dès que le fichier existe dans public/catalog/.
            $image = 'catalog/'.Str::slug($name).'.jpg';
            if ($product->image_path === null && file_exists(public_path($image))) {
                $product->update(['image_path' => $image]);
            }
        }
    }

    /** @return array<int, array{0: string, 1: string, 2: int, 3: array<int, string>}> */
    private function products(): array
    {
        $v = self::VETEMENT;
        $c = self::CHAUSSURE;
        $u = self::UNIQUE;

        return [
            // Vestes et manteaux
            ['Veste Chore Minuit', 'Veste de travail en coton épais, bleu nuit, quatre poches plaquées.', 129, $v],
            ['Bomber Satin Aurore', 'Bomber en satin réversible, doublure orange, bords côtelés.', 149, $v],
            ['Doudoune Nuage', 'Doudoune légère matelassée, capuche rétractable, très compressible.', 189, $v],
            ['Parka Gabardine Sable', 'Parka mi-longue en gabardine déperlante, capuche doublée.', 169, $v],
            ['Veste en Jean Délavée', 'Veste en denim délavé à la main, coupe droite, boutons métal.', 119, $v],
            ['Coupe-vent Rivage', 'Coupe-vent fin à capuche, imperméable, se range dans sa poche.', 99, $v],
            // Sweats et mailles
            ['Hoodie Brique', 'Sweat à capuche en molleton épais, coloris brique, poche kangourou.', 89, $v],
            ['Sweat Col Rond Ardoise', 'Sweat col rond gris ardoise, coton bio, coupe ample.', 69, $v],
            ['Crewneck Brodé Marguerite', 'Sweat col rond avec marguerite brodée sur la poitrine.', 79, $v],
            ['Zip Hoodie Cobalt', 'Sweat zippé bleu cobalt, zip métal, poches latérales.', 95, $v],
            ['Pull Maille Torsadée', 'Pull en laine mélangée à grosses torsades, col rond.', 109, $v],
            ['Sweat Oversize Écru', 'Sweat oversize écru, épaules tombantes, coton lourd.', 75, $v],
            // Hauts
            ['T-shirt Boxy Blanc Craie', 'T-shirt court et large, jersey de coton 220 g, blanc craie.', 39, $v],
            ['T-shirt Imprimé Soleil', 'T-shirt avec soleil sérigraphié dans le dos, coton bio.', 45, $v],
            ['Débardeur Côtelé Sable', 'Débardeur en maille côtelée, coupe près du corps, sable.', 29, $v],
            ['Polo Maille Piquée', 'Polo en maille piquée, col boutonné, coloris bleu marine.', 59, $v],
            ['T-shirt Manches Longues Rayé', 'Marinière à manches longues, rayures bleues et écrues.', 49, $v],
            ['Chemise Lin Azur', 'Chemise en lin lavé, bleu azur, manches retroussables.', 85, $v],
            // Bas
            ['Cargo Kaki', 'Pantalon cargo kaki, six poches, taille ajustable.', 99, $v],
            ['Jean Droit Brut', 'Jean droit en denim brut 13 oz, bleu profond.', 109, $v],
            ['Pantalon à Pinces Anthracite', 'Pantalon à pinces fluide, gris anthracite, taille haute.', 119, $v],
            ['Jogging Fuselé Gris Chiné', 'Jogging fuselé en molleton, gris chiné, cordon plat.', 65, $v],
            ['Short Ripstop Olive', 'Short en ripstop olive, séchage rapide, poches zippées.', 55, $v],
            ['Jupe Plissée Bleu Nuit', 'Jupe plissée mi-longue, bleu nuit, taille élastiquée.', 79, $v],
            ['Pantalon Parachute Noir', 'Pantalon parachute noir, jambes larges, bas à cordon.', 89, $v],
            // Chaussures
            ['Sneakers Midnight Runner', 'Sneakers de running urbain, semelle amortie, bleu nuit et orange.', 129, $c],
            ['Sneakers Basses Blanc Cassé', 'Sneakers basses en cuir blanc cassé, semelle gomme.', 119, $c],
            ['Montantes Cobalt', 'Sneakers montantes en toile bleu cobalt, bout renforcé.', 139, $c],
            ['Runners Mesh Orange', 'Runners en mesh respirant, orange vif, semelle légère.', 125, $c],
            ['Mocassins Daim Camel', 'Mocassins en daim camel, semelle souple, finition cousue main.', 149, $c],
            ['Bottines Chelsea Cuir', 'Bottines Chelsea en cuir lisse, élastiques latéraux.', 159, $c],
            ['Sandales Cuir Naturel', 'Sandales à brides en cuir naturel, semelle liège.', 89, $c],
            ['Sneakers Suède Sable', 'Sneakers en suède sable, lacets plats, semelle crêpe.', 135, $c],
            // Casquettes et textiles d'hiver
            ['Casquette Brodée Fleur', 'Casquette six panneaux avec fleur brodée, réglable.', 35, $u],
            ['Bonnet Côtelé Ocre', 'Bonnet en maille côtelée ocre, revers épais.', 25, $u],
            ['Bob Coton Bleu', 'Bob en coton bleu, bords souples, œillets d\'aération.', 39, $u],
            ['Casquette Crème Six Panneaux', 'Casquette crème en coton, fermeture cuir, sans logo.', 32, $u],
            ['Écharpe Laine Brique', 'Écharpe en laine brique, franges, 190 cm.', 45, $u],
            // Sacs et accessoires
            ['Tote Bag Toile Épaisse', 'Tote bag en toile épaisse, anses longues, poche intérieure.', 29, $u],
            ['Sac Banane Technique', 'Sac banane en nylon technique, deux compartiments zippés.', 49, $u],
            ['Sac à Dos Roll-Top', 'Sac à dos roll-top imperméable, 22 litres, dos matelassé.', 89, $u],
            ['Ceinture Cuir Tressé', 'Ceinture en cuir tressé, boucle métal brossé.', 45, $u],
            ['Portefeuille Cuir Compact', 'Portefeuille compact en cuir, six cartes et un billet.', 55, $u],
            ['Lunettes Écaille Mer', 'Lunettes de soleil écaille, verres teintés bleu mer.', 79, $u],
            ['Chaussettes Pack Trois', 'Trois paires de chaussettes mi-hautes, coton peigné.', 19, $u],
            ['Gants Laine Gris', 'Gants en laine grise, doublure polaire, écrans tactiles.', 35, $u],
            ['Foulard Soie Imprimé', 'Foulard en soie imprimé fleurs orange sur fond bleu.', 59, $u],
            ['Porte-clés Cuir Brodé', 'Porte-clés en cuir avec fleur brodée, anneau métal.', 15, $u],
            ['Besace Cuir Cognac', 'Besace en cuir cognac, bandoulière réglable, deux poches.', 139, $u],
            ['Montre Cadran Bleu Acier', 'Montre à cadran bleu acier, bracelet cuir, résistante à l\'eau.', 99, $u],
        ];
    }
}
