# Collection
Collection management, heavily inspired by Doctrine ArrayCollection

# Install

```
composer require amo/collection
```

# Usages

```PHP
// Static instantiation
Collection::make($repository->findBy($criterias))
    // map method, allows to create a new collection 
    // based on each item of the given collection 
    ->map(function(User $user){
        return $user->getEmail();
    })
    // each method, executes a closure fore each item of a collection
    ->each(function(String $email) {
        $message = $this->buildMessage($email);
        $this->mailer->send($message);
    });
    // etc...
```
