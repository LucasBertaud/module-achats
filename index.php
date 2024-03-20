<?php
try {
    $user = "root";
    $password = "root";
    $ip = '127.0.0.1';
    $port = '8889';
    $port = $port != '' ? ':'.$port : '';
    $dsn = 'mysql:dbname=gestion_achats;host='.$ip.$port;
    $pdo = new PDO($dsn, $user, $password);
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        $quantity = $_POST["quantity"];
        $category = $_POST["category"];
        if (isset($_POST["id"])) {
            $id = $_POST["id"];
            $pdo->exec("CALL modifier_commande(".$id.",".$quantity.", ".$category.")");
        }else{
            $pdo->exec("CALL creer_commande(".$quantity.", ".$category.")");
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents("php://input"), $_DELETE);
        $id = $_DELETE["id"];
        $pdo->exec("CALL supprimer_commande(".$id.")");
    }
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10;
    $offset = intval($page - 1 . '0');
    $commandes = $pdo->query('CALL select_commandes('.$offset.','.$limit.')')->fetchAll();
    $total = $pdo->query('SELECT COUNT(*) as total FROM `commandes` ORDER BY `date_commande`;')->fetch();
    $page_total = $pdo->query('SELECT COUNT(*) as total FROM (SELECT * FROM `commandes` ORDER BY `date_commande` DESC LIMIT '.$offset.','.$limit.') as a;')->fetch();
    $produits = $pdo->query('SELECT * FROM `produits`;')->fetchAll();
    $template_array_id = ["apple-imac-20-dropdown", "apple-imac-27-dropdown", "apple-iphone-14-dropdown", "apple-ipad-air-dropdown", "xbox-series-s-dropdown", "playstation-5-dropdown", "xbox-series-x-dropdown", "apple-watch-se-dropdown", "nikon-d850-dropdown", "benq-ex2710q-dropdown"];
    $i = 0;
} catch (\Throwable $th) {
    $path = __DIR__.'/gestion_achats.sql';
    if (file_exists($path)) {
        $file = file_get_contents($path);
        $dsn = 'mysql:host='.$ip.$port;
        $pdo = new PDO($dsn, $user, $password);
        $pdo->exec($file);
        header("Refresh:0");
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD - Module Achats</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css"  rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js" defer></script>
    <script src="TotalPrice.js" defer></script>
    <script src="DeleteOrder.js" defer></script>
    <script src="UpdateOrder.js" defer></script>
    <script src="AlertModal.js" defer></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                colors: {
                    primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"}
                }
                },
                fontFamily: {
                'body': [
                'Inter', 
                'ui-sans-serif', 
                'system-ui', 
                '-apple-system', 
                'system-ui', 
                'Segoe UI', 
                'Roboto', 
                'Helvetica Neue', 
                'Arial', 
                'Noto Sans', 
                'sans-serif', 
                'Apple Color Emoji', 
                'Segoe UI Emoji', 
                'Segoe UI Symbol', 
                'Noto Color Emoji'
            ],
                'sans': [
                'Inter', 
                'ui-sans-serif', 
                'system-ui', 
                '-apple-system', 
                'system-ui', 
                'Segoe UI', 
                'Roboto', 
                'Helvetica Neue', 
                'Arial', 
                'Noto Sans', 
                'sans-serif', 
                'Apple Color Emoji', 
                'Segoe UI Emoji', 
                'Segoe UI Symbol', 
                'Noto Color Emoji'
            ]
                }
            }
            }
    </script>
</head>
<body>
        <!-- Start block -->
    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5 antialiased" style="height: 100vh;">
        <div class="mx-auto max-w-screen-xl px-4 lg:px-12">
            <!-- Start coding here -->
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                    <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                        <button type="button" id="createProductModalButton" data-modal-target="createProductModal" data-modal-toggle="createProductModal" class="flex items-center justify-center text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800 transition">
                            <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                            </svg>
                            Ajouter une commande
                        </button>
                        <div class="flex items-center space-x-3 w-full md:w-auto">
                            <div id="actionsDropdown" class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                                <ul class="py-1 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="actionsDropdownButton">
                                </ul>
                            </div>
                            <div id="filterDropdown" class="z-10 hidden w-56 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                                <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Category</h6>
                                <ul class="space-y-2 text-sm" aria-labelledby="filterDropdownButton">
                                    <li class="flex items-center">
                                        <input id="apple" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="apple" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Apple (56)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="fitbit" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="fitbit" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Fitbit (56)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="dell" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="dell" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Dell (56)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="asus" type="checkbox" value="" checked="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="asus" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Asus (97)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="logitech" type="checkbox" value="" checked="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="logitech" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Logitech (97)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="msi" type="checkbox" value="" checked="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="msi" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">MSI (97)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="bosch" type="checkbox" value="" checked="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="bosch" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Bosch (176)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="sony" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="sony" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Sony (234)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="samsung" type="checkbox" value="" checked="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="samsung" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Samsung (76)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="canon" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="canon" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Canon (49)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="microsoft" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="microsoft" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Microsoft (45)</label>
                                    </li>
                                    <li class="flex items-center">
                                        <input id="razor" type="checkbox" value="" class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="razor" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Razor (49)</label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <button id="btn-modal-alert" class="bg-primary-700 hover:bg-primary-800 transition rounded-full p-3 flex items-center justify-center">
                        <img src="img/bell.svg" class="size-5">
                    </button>
                    <div id="modal-alert" class="hidden border rounded absolute right-0 top-16 bg-white px-4 h-1/2 overflow-auto">
                        <ul>
                            <?php
                                $query = $pdo->query('SELECT type_alerte FROM `alertes`;')->fetchAll();
                                if ($query) {
                                    foreach($query as $item) {
                                        echo '<li class="py-4 border-b last:border-b-0 flex items-center justify-between gap-24">
                                                <span>
                                                   ' . $item[0] . '
                                                </span>
                                                <button type="submit">
                                                    <img src="img/cross.svg" class="size-4">
                                                </button>
                                            </li>';
                                    }
                                } else {
                                    echo "Aucune alerte trouvé.";
                                }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-4 py-4">Nom Produit</th>
                                <th scope="col" class="px-4 py-3">Quantité</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3">Fournisseur</th>
                                <th scope="col" class="px-4 py-3">Date Commande</th>
                                <th scope="col" class="px-4 py-3">Prix Total</th>
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($commandes as $commande):
                            ?>
                            <tr class="border-b dark:border-gray-700">
                                <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <?php 
                                        $query = $pdo->query('SELECT * FROM `produits` WHERE `id` = '.$commande["id_produit"].';')->fetch();
                                        if ($query) {
                                            echo $query["nom"];
                                        } else{
                                            echo "Produit introuvable";
                                        }
                                    ?>
                                </th>
                                <td class="px-4 py-3"><?= $commande["quantite"] ?></td>
                                <td class="px-4 py-3"><?= $commande["statut"] ?></td>
                                <td class="px-4 py-3">
                                    <?php
                                        $query = $pdo->query('SELECT * FROM `fournisseurs` WHERE `id` = '.$commande["id_fournisseur"].';')->fetch();
                                        if ($query) {
                                            echo $query["nom"];
                                        } else{
                                            echo "Fournisseur introuvable";
                                        }
                                    ?>
                                </td>
                                <td class="px-4 py-3"><?= $commande["date_commande"] ?></td>
                                <td class="px-4 py-3"><?= $commande["prix"] ?>€</td>
                                <td class="px-4 py-3 flex items-center justify-end">
                                    <button id="<?= $template_array_id[$i] ?>-button" data-dropdown-toggle="<?= $template_array_id[$i] ?>" class="inline-flex items-center text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700 p-1.5 dark:hover-bg-gray-800 text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none dark:text-gray-400 dark:hover:text-gray-100" type="button">
                                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                        </svg>
                                    </button>
                                    <div id="<?= $template_array_id[$i] ?>" class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                                        <ul class="py-1 text-sm" aria-labelledby="apple-imac-27-dropdown-button">
                                            <li>
                                                <button type="button" id="update-btn" data-order-id="<?= $commande["id"] ?>" data-modal-target="updateProductModal" data-modal-toggle="updateProductModal" class="flex w-full items-center py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white text-gray-700 dark:text-gray-200">
                                                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                                                    </svg>
                                                    Modifier
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" id="delete-btn" data-order-id="<?= $commande["id"] ?>" data-modal-target="deleteModal" data-modal-toggle="deleteModal" class="flex w-full items-center py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 text-red-500 dark:hover:text-red-400">
                                                    <svg class="w-4 h-4 mr-2" viewbox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" fill="currentColor" d="M6.09922 0.300781C5.93212 0.30087 5.76835 0.347476 5.62625 0.435378C5.48414 0.523281 5.36931 0.649009 5.29462 0.798481L4.64302 2.10078H1.59922C1.36052 2.10078 1.13161 2.1956 0.962823 2.36439C0.79404 2.53317 0.699219 2.76209 0.699219 3.00078C0.699219 3.23948 0.79404 3.46839 0.962823 3.63718C1.13161 3.80596 1.36052 3.90078 1.59922 3.90078V12.9008C1.59922 13.3782 1.78886 13.836 2.12643 14.1736C2.46399 14.5111 2.92183 14.7008 3.39922 14.7008H10.5992C11.0766 14.7008 11.5344 14.5111 11.872 14.1736C12.2096 13.836 12.3992 13.3782 12.3992 12.9008V3.90078C12.6379 3.90078 12.8668 3.80596 13.0356 3.63718C13.2044 3.46839 13.2992 3.23948 13.2992 3.00078C13.2992 2.76209 13.2044 2.53317 13.0356 2.36439C12.8668 2.1956 12.6379 2.10078 12.3992 2.10078H9.35542L8.70382 0.798481C8.62913 0.649009 8.5143 0.523281 8.37219 0.435378C8.23009 0.347476 8.06631 0.30087 7.89922 0.300781H6.09922ZM4.29922 5.70078C4.29922 5.46209 4.39404 5.23317 4.56282 5.06439C4.73161 4.8956 4.96052 4.80078 5.19922 4.80078C5.43791 4.80078 5.66683 4.8956 5.83561 5.06439C6.0044 5.23317 6.09922 5.46209 6.09922 5.70078V11.1008C6.09922 11.3395 6.0044 11.5684 5.83561 11.7372C5.66683 11.906 5.43791 12.0008 5.19922 12.0008C4.96052 12.0008 4.73161 11.906 4.56282 11.7372C4.39404 11.5684 4.29922 11.3395 4.29922 11.1008V5.70078ZM8.79922 4.80078C8.56052 4.80078 8.33161 4.8956 8.16282 5.06439C7.99404 5.23317 7.89922 5.46209 7.89922 5.70078V11.1008C7.89922 11.3395 7.99404 11.5684 8.16282 11.7372C8.33161 11.906 8.56052 12.0008 8.79922 12.0008C9.03791 12.0008 9.26683 11.906 9.43561 11.7372C9.6044 11.5684 9.69922 11.3395 9.69922 11.1008V5.70078C9.69922 5.46209 9.6044 5.23317 9.43561 5.06439C9.26683 4.8956 9.03791 4.80078 8.79922 4.80078Z" />
                                                    </svg>
                                                    Supprimer
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php
                                $i++;
                                endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
                <nav class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4" aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                        Showing
                        <span class="font-semibold text-gray-900 dark:text-white">
                            <?php 
                            
                            if ($page_total["total"] > 0) {
                                // page 1 mini = 1 ; page 2 mini = 11 ; page 3 mini = 21
                                $min = $offset + 1;
                                $max = $page_total["total"] + $offset;
                                echo $min . '-' . $max;
                            }else {
                                echo 'null';
                            }
                            ?></span>
                        of
                        <span class="font-semibold text-gray-900 dark:text-white"><?= $total["total"] ?></span>
                    </span>
                    <ul class="inline-flex items-stretch -space-x-px">
                        <li>
                            <a href="?page=<?= $page != 1 ? $page-1 : 1?>" class="flex items-center justify-center h-full py-1.5 px-3 ml-0 text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <span class="sr-only">Previous</span>
                                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="?page=<?= $page ?>" class="flex items-center justify-center text-sm z-10 py-2 px-3 leading-tight text-primary-600 bg-primary-50 border border-primary-300 hover:bg-primary-100 hover:text-primary-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white"><?= $page ?></a>
                        </li>
                        <li>
                            <a href="?page=<?= $page_total["total"] == 10 ? $page+1 : $page?>" class="flex items-center justify-center h-full py-1.5 px-3 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <span class="sr-only">Next</span>
                                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </section>
    <!-- End block -->
    <!-- Create modal -->
    <div id="createProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                <!-- Modal header -->
                <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5 dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ajouter Commande</h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-target="createProductModal" data-modal-toggle="createProductModal">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form action="" method="POST">
                    <div class="grid gap-4 mb-4 sm:grid-cols-2">
                        <div>
                            <label for="quantity" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Quantité</label>
                            <input type="number" name="quantity" min="1" max="100" id="quantity" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="0" required="true">
                        </div>
                        <div>
                            <label for="price" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Prix</label>
                            <input type="number" name="price" id="price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Prix total" readonly>
                        </div>
                        <div>
                            <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Produit</label>
                            <select name="category" required id="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <option selected="">Selectionner un produit</option>
                                <?php
                                    foreach ($produits as $produit) :
                                ?>
                                <option data-unit-price="<?= $produit["prix_unitaire"] ?>" value="<?= $produit["id"] ?>"><?= $produit["nom"] ?></option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="text-white inline-flex items-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        <svg class="mr-1 -ml-1 w-6 h-6" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Ajouter une nouvelle commande
                    </button>
                </form>
            </div>
        </div>
    </div>
    <!-- Update modal -->
    <div id="updateProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                <!-- Modal header -->
                <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5 dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Modifier Commande</h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="updateProductModal">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form action="" method="POST" name="update">
                    <input type="hidden" value="" name="id" id="hidden-id" />
                    <div class="grid gap-4 mb-4 sm:grid-cols-2">
                        <div>
                            <label for="quantity" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Quantité</label>
                            <input type="number" name="quantity" id="quantity" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="0" required="true">
                        </div>
                        <div>
                            <label for="price" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Prix</label>
                            <input type="number" min="1" max="100" name="price" id="price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Prix total" disabled>
                        </div>
                        <div>
                            <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Produit</label>
                            <select id="category" name="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <option selected="">Selectionner un produit</option>
                                <?php
                                    foreach ($produits as $produit) :
                                ?>
                                <option data-unit-price="<?= $produit["prix_unitaire"] ?>" value="<?= $produit["id"] ?>"><?= $produit["nom"] ?></option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="text-white inline-flex items-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        <svg class="mr-1 -ml-1 w-6 h-6" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Modifier une commande
                    </button>
                </form>
            </div>
        </div>
    </div>
    <!-- Read modal -->
    <div id="readProductModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-xl max-h-full">
            <!-- Modal content -->
            <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                <!-- Modal header -->
                <div class="flex justify-between mb-4 rounded-t sm:mb-5">
                    <div class="text-lg text-gray-900 md:text-xl dark:text-white">
                        <h3 class="font-semibold ">Apple iMac 27”</h3>
                        <p class="font-bold">$2999</p>
                    </div>
                    <div>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="readProductModal">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                </div>
                <dl><dt class="mb-2 font-semibold leading-none text-gray-900 dark:text-white">Details</dt><dd class="mb-4 font-light text-gray-500 sm:mb-5 dark:text-gray-400">Standard glass ,3.8GHz 8-core 10th-generation Intel Core i7 processor, Turbo Boost up to 5.0GHz, 16GB 2666MHz DDR4 memory, Radeon Pro 5500 XT with 8GB of GDDR6 memory, 256GB SSD storage, Gigabit Ethernet, Magic Mouse 2, Magic Keyboard - US.</dd><dt class="mb-2 font-semibold leading-none text-gray-900 dark:text-white">Category</dt><dd class="mb-4 font-light text-gray-500 sm:mb-5 dark:text-gray-400">Electronics/PC</dd></dl>
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <button type="button" class="text-white inline-flex items-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                            <svg aria-hidden="true" class="mr-1 -ml-1 w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                            </svg>
                            Modifier
                        </button>
                        <button type="button" class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Preview</button>
                    </div>
                    <button type="button" class="inline-flex items-center text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">
                        <svg aria-hidden="true" class="w-5 h-5 mr-1.5 -ml-1" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Delete modal -->
    <div id="deleteModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                <button type="button" class="text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="deleteModal">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <svg class="text-gray-400 dark:text-gray-500 w-11 h-11 mb-3.5 mx-auto" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <p class="mb-4 text-gray-500 dark:text-gray-300">Êtes-vous sûr de vouloir supprimer cet élément ?</p>
                <div class="flex justify-center items-center space-x-4">
                    <button data-modal-toggle="deleteModal" type="button" class="py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Non, annuler</button>
                        <button type="button" id="confirm-delete" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">Oui, je suis sûr</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>