import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ArticleController::recent
 * @see app/Http/Controllers/ArticleController.php:28
 * @route '/articles/recent'
 */
export const recent = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recent.url(options),
    method: 'get',
})

recent.definition = {
    methods: ["get","head"],
    url: '/articles/recent',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ArticleController::recent
 * @see app/Http/Controllers/ArticleController.php:28
 * @route '/articles/recent'
 */
recent.url = (options?: RouteQueryOptions) => {
    return recent.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ArticleController::recent
 * @see app/Http/Controllers/ArticleController.php:28
 * @route '/articles/recent'
 */
recent.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recent.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ArticleController::recent
 * @see app/Http/Controllers/ArticleController.php:28
 * @route '/articles/recent'
 */
recent.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: recent.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ArticleController::index
 * @see app/Http/Controllers/ArticleController.php:15
 * @route '/articles'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/articles',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ArticleController::index
 * @see app/Http/Controllers/ArticleController.php:15
 * @route '/articles'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ArticleController::index
 * @see app/Http/Controllers/ArticleController.php:15
 * @route '/articles'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ArticleController::index
 * @see app/Http/Controllers/ArticleController.php:15
 * @route '/articles'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ArticleController::show
 * @see app/Http/Controllers/ArticleController.php:74
 * @route '/articles/{article}'
 */
export const show = (args: { article: number | { id: number } } | [article: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/articles/{article}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ArticleController::show
 * @see app/Http/Controllers/ArticleController.php:74
 * @route '/articles/{article}'
 */
show.url = (args: { article: number | { id: number } } | [article: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { article: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { article: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    article: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        article: typeof args.article === 'object'
                ? args.article.id
                : args.article,
                }

    return show.definition.url
            .replace('{article}', parsedArgs.article.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ArticleController::show
 * @see app/Http/Controllers/ArticleController.php:74
 * @route '/articles/{article}'
 */
show.get = (args: { article: number | { id: number } } | [article: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ArticleController::show
 * @see app/Http/Controllers/ArticleController.php:74
 * @route '/articles/{article}'
 */
show.head = (args: { article: number | { id: number } } | [article: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ArticleController::store
 * @see app/Http/Controllers/ArticleController.php:41
 * @route '/articles'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/articles',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ArticleController::store
 * @see app/Http/Controllers/ArticleController.php:41
 * @route '/articles'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ArticleController::store
 * @see app/Http/Controllers/ArticleController.php:41
 * @route '/articles'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ArticleController::update
 * @see app/Http/Controllers/ArticleController.php:81
 * @route '/articles/{article}'
 */
export const update = (args: { article: number | { id: number } } | [article: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/articles/{article}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\ArticleController::update
 * @see app/Http/Controllers/ArticleController.php:81
 * @route '/articles/{article}'
 */
update.url = (args: { article: number | { id: number } } | [article: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { article: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { article: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    article: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        article: typeof args.article === 'object'
                ? args.article.id
                : args.article,
                }

    return update.definition.url
            .replace('{article}', parsedArgs.article.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ArticleController::update
 * @see app/Http/Controllers/ArticleController.php:81
 * @route '/articles/{article}'
 */
update.put = (args: { article: number | { id: number } } | [article: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\ArticleController::update
 * @see app/Http/Controllers/ArticleController.php:81
 * @route '/articles/{article}'
 */
update.patch = (args: { article: number | { id: number } } | [article: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\ArticleController::destroy
 * @see app/Http/Controllers/ArticleController.php:112
 * @route '/articles/{article}'
 */
export const destroy = (args: { article: number | { id: number } } | [article: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/articles/{article}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\ArticleController::destroy
 * @see app/Http/Controllers/ArticleController.php:112
 * @route '/articles/{article}'
 */
destroy.url = (args: { article: number | { id: number } } | [article: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { article: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { article: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    article: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        article: typeof args.article === 'object'
                ? args.article.id
                : args.article,
                }

    return destroy.definition.url
            .replace('{article}', parsedArgs.article.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ArticleController::destroy
 * @see app/Http/Controllers/ArticleController.php:112
 * @route '/articles/{article}'
 */
destroy.delete = (args: { article: number | { id: number } } | [article: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})
const ArticleController = { recent, index, show, store, update, destroy }

export default ArticleController