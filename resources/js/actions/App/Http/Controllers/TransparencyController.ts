import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\TransparencyController::index
 * @see app/Http/Controllers/TransparencyController.php:10
 * @route '/transparency'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/transparency',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TransparencyController::index
 * @see app/Http/Controllers/TransparencyController.php:10
 * @route '/transparency'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TransparencyController::index
 * @see app/Http/Controllers/TransparencyController.php:10
 * @route '/transparency'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TransparencyController::index
 * @see app/Http/Controllers/TransparencyController.php:10
 * @route '/transparency'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})
const TransparencyController = { index }

export default TransparencyController