# Configuration pour les tests d'intégration des repositories

## Prérequis

Pour exécuter les tests d'intégration des repositories, vous devez :

1. **Configurer une base de données de test** dans votre `.env.test` :
   ```
   DATABASE_URL="mysql://user:password@127.0.0.1:3306/your_project_test?serverVersion=8.0.32&charset=utf8mb4"
   ```

2. **Créer la base de données de test** :
   ```bash
   php bin/console doctrine:database:create --env=test
   php bin/console doctrine:schema:create --env=test
   ```

3. **Optionnel - Charger des fixtures de test** :
   ```bash
   php bin/console doctrine:fixtures:load --env=test --no-interaction
   ```

## Types de tests pour repositories

### 1. Tests d'intégration (avec base de données)
- Testent les vraies requêtes SQL
- Utilisent `KernelTestCase`
- Nécessitent une base de données de test
- Plus lents mais plus réalistes

**Exemple :**
```php
class UtilisateurRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }
}
```

### 2. Tests unitaires (avec mocks)
- Testent la logique sans base de données
- Utilisent `TestCase`
- Plus rapides
- Nécessitent plus de setup avec des mocks

**Exemple :**
```php
class PostRepositoryUnitTest extends TestCase
{
    private PostRepository $repository;
    private EntityManagerInterface $entityManager;
    
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new PostRepository($registry);
    }
}
```

## Méthodes utiles à ajouter dans vos repositories

### UtilisateurRepository
```php
public function findActiveUsers(): array
{
    return $this->createQueryBuilder('u')
        ->andWhere('u.lastVisit > :date')
        ->setParameter('date', new \DateTime('-30 days'))
        ->orderBy('u.lastVisit', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findUsersByRole(string $role): array
{
    return $this->createQueryBuilder('u')
        ->andWhere('u.roles LIKE :role')
        ->setParameter('role', '%"' . $role . '"%')
        ->getQuery()
        ->getResult();
}
```

### ForumRepository
```php
public function findVisibleForums(): array
{
    return $this->createQueryBuilder('f')
        ->andWhere('f.visible = :visible')
        ->setParameter('visible', true)
        ->orderBy('f.ordreAffichage', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findActiveForums(): array
{
    return $this->createQueryBuilder('f')
        ->andWhere('f.dateCloture IS NULL OR f.dateCloture > :now')
        ->setParameter('now', new \DateTime())
        ->andWhere('f.visible = :visible')
        ->setParameter('visible', true)
        ->getQuery()
        ->getResult();
}
```

### MachineRepository
```php
public function findByUuid(Uuid $uuid): ?Machine
{
    return $this->createQueryBuilder('m')
        ->andWhere('m.uuid = :uuid')
        ->setParameter('uuid', $uuid)
        ->getQuery()
        ->getOneOrNullResult();
}

public function findVisibleMachines(): array
{
    return $this->createQueryBuilder('m')
        ->andWhere('m.visible = :visible')
        ->setParameter('visible', true)
        ->orderBy('m.name', 'ASC')
        ->getQuery()
        ->getResult();
}
```

## Exécution des tests

### Tous les tests de repository :
```bash
./vendor/bin/phpunit tests/Repository/
```

### Tests d'intégration uniquement :
```bash
./vendor/bin/phpunit tests/Repository/ --exclude-group unit
```

### Tests unitaires uniquement :
```bash
./vendor/bin/phpunit tests/Repository/ --group unit
```

### Avec coverage :
```bash
./vendor/bin/phpunit tests/Repository/ --coverage-html coverage/
```
