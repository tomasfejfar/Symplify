#!/usr/bin/env bash
git subsplit init git@github.com:symplify/symplify.git

LAST_TAG=$(git tag -l  --sort=committerdate | tail -n1);

# Symplify
git subsplit publish --heads="master" --tags=$LAST_TAG packages/CodingStandard:git@github.com:Symplify/CodingStandard.git
git subsplit publish --heads="master" --tags=$LAST_TAG packages/DefaultAutowire:git@github.com:Symplify/DefaultAutowire.git
git subsplit publish --heads="master" --tags=$LAST_TAG packages/DefaultAutoconfigure:git@github.com:Symplify/DefaultAutoconfigure.git
git subsplit publish --heads="master" --tags=$LAST_TAG packages/EasyCodingStandard:git@github.com:Symplify/EasyCodingStandard.git
git subsplit publish --heads="master" --tags=$LAST_TAG packages/ModularLatteFilters:git@github.com:Symplify/ModularLatteFilters.git
git subsplit publish --heads="master" --tags=$LAST_TAG packages/ModularRouting:git@github.com:Symplify/ModularRouting.git
git subsplit publish --heads="master" --tags=$LAST_TAG packages/PackageBuilder:git@github.com:Symplify/PackageBuilder.git
git subsplit publish --heads="master" --tags=$LAST_TAG packages/Statie:git@github.com:Symplify/Statie.git
git subsplit publish --heads="master" --tags=$LAST_TAG packages/SymbioticController:git@github.com:Symplify/SymbioticController.git
git subsplit publish --heads="master" --tags=$LAST_TAG packages/SymfonyEventDispatcher:git@github.com:Symplify/SymfonyEventDispatcher.git

rm -rf .subsplit/

# inspired by laravel: https://github.com/laravel/framework/blob/5.4/build/illuminate-split-full.sh
# they use SensioLabs now though: https://github.com/laravel/framework/pull/17048#issuecomment-269915319